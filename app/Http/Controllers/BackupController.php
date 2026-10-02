<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class BackupController extends Controller
{
    private function validName(string $file): void
    {
        abort_unless(preg_match('/^backup_[0-9_]+\.(sql|sql\.gz)$/', $file), 404);
    }

    public function index()
    {
        return view('backup.index', ['files' => collect(Storage::disk('local')->files('backups'))->sortDesc()]);
    }

    public function create()
    {
        $dir = storage_path('app/backups');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $base = 'backup_' . now()->format('Ymd_His');
        $sqlPath = $dir . DIRECTORY_SEPARATOR . $base . '.sql';
        $gzPath = $sqlPath . '.gz';

        $mysqldump = env('MYSQLDUMP_PATH', 'mysqldump');
        $args = [$mysqldump, '--host=' . env('DB_HOST', '127.0.0.1'), '--port=' . env('DB_PORT', '3306'), '--user=' . env('DB_USERNAME', 'root')];
        if ((string) env('DB_PASSWORD', '') !== '') {
            $args[] = '--password=' . env('DB_PASSWORD');
        }
        $args[] = env('DB_DATABASE');

        $process = new Process($args);
        $process->setTimeout(300);
        $process->run(function ($type, $buffer) use ($sqlPath) {
            if ($type === Process::OUT) {
                file_put_contents($sqlPath, $buffer, FILE_APPEND);
            }
        });

        if (! $process->isSuccessful() || ! is_file($sqlPath) || filesize($sqlPath) === 0) {
            @unlink($sqlPath);
            return back()->withErrors(['backup' => 'Backup دروست نەکرا. دڵنیابە لە MYSQLDUMP_PATH و زانیاری DB.']);
        }

        $in = fopen($sqlPath, 'rb');
        $out = gzopen($gzPath, 'wb9');
        while (! feof($in)) { gzwrite($out, fread($in, 1024 * 1024)); }
        fclose($in); gzclose($out); unlink($sqlPath);

        $files = collect(Storage::disk('local')->files('backups'))
            ->filter(fn ($f) => preg_match('/^backups\/backup_[0-9_]+\.sql\.gz$/', $f))
            ->sortDesc();
        foreach ($files->slice(14) as $old) { Storage::disk('local')->delete($old); }

        return back()->with('success', 'Backup بە سەرکەوتوویی دروستکرا.');
    }

    public function download(string $file)
    {
        $this->validName($file);
        abort_unless(Storage::disk('local')->exists('backups/' . $file), 404);
        return Storage::disk('local')->download('backups/' . $file, $file);
    }

    public function destroy(string $file)
    {
        $this->validName($file);
        Storage::disk('local')->delete('backups/' . $file);
        return back()->with('success', 'Backup سڕایەوە.');
    }
}
