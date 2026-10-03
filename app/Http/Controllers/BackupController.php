<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class BackupController extends Controller
{
    private function validName(string $file): void
    {
        abort_unless(preg_match('/^backup_[0-9_]+\.sql\.gz$/', $file), 404);
    }

    public function index()
    {
        $files = collect(Storage::disk('local')->files('backups'))
            ->filter(fn ($f) => preg_match('/^backups\/backup_[0-9_]+\.sql\.gz$/', $f))
            ->sortDesc()
            ->values();

        return view('backup.index', ['files' => $files]);
    }

    public function create()
    {
        $disk = Storage::disk('local');
        $disk->makeDirectory('backups');

        $base = 'backup_' . now()->format('Ymd_His');
        $relativeSql = 'backups/' . $base . '.sql';
        $relativeGz = $relativeSql . '.gz';
        $sqlPath = $disk->path($relativeSql);
        $gzPath = $disk->path($relativeGz);

        try {
            $this->writeSqlDump($sqlPath);
            $this->gzipFile($sqlPath, $gzPath);
            @unlink($sqlPath);

            $this->pruneOldBackups($disk);

            // Browser receives the backup immediately as a file download.
            return $disk->download($relativeGz, basename($relativeGz), [
                'Content-Type' => 'application/gzip',
                'Content-Disposition' => 'attachment; filename="' . basename($relativeGz) . '"',
            ]);
        } catch (\Throwable $e) {
            @unlink($sqlPath);
            @unlink($gzPath);
            report($e);

            throw ValidationException::withMessages([
                'backup' => 'Backup دروست نەکرا. دڵنیابە لە MySQL و ڕێگەپێدانی نووسین لە storage. هۆکار لە log ـدا تۆمارکراوە.',
            ]);
        }
    }

    private function writeSqlDump(string $path): void
    {
        $pdo = DB::connection()->getPdo();
        $handle = fopen($path, 'wb');
        if ($handle === false) {
            throw new \RuntimeException('Cannot create backup file.');
        }

        $started = false;
        try {
            fwrite($handle, "-- Sweet Shop POS database backup\n");
            fwrite($handle, "-- Created: " . now()->toDateTimeString() . "\n\n");
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
            fwrite($handle, "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n");

            $pdo->exec('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ');
            $pdo->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT');
            $started = true;

            $tables = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(\PDO::FETCH_NUM);
            $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);

            foreach ($tables as $tableRow) {
                $table = $tableRow[0];
                $quotedTable = $this->quoteIdentifier($table);
                $create = $pdo->query("SHOW CREATE TABLE {$quotedTable}")->fetch(\PDO::FETCH_NUM);
                if (! $create) {
                    continue;
                }

                fwrite($handle, "DROP TABLE IF EXISTS {$quotedTable};\n");
                fwrite($handle, $create[1] . ";\n\n");

                $stmt = $pdo->query("SELECT * FROM {$quotedTable}");
                $columns = [];
                $insertPrefix = null;

                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    if ($insertPrefix === null) {
                        $columns = array_map(fn ($column) => $this->quoteIdentifier($column), array_keys($row));
                        $insertPrefix = "INSERT INTO {$quotedTable} (" . implode(', ', $columns) . ") VALUES ";
                    }

                    $values = [];
                    foreach ($row as $value) {
                        $values[] = $value === null ? 'NULL' : $pdo->quote((string) $value);
                    }
                    fwrite($handle, $insertPrefix . '(' . implode(', ', $values) . ");\n");
                }

                $stmt->closeCursor();
                fwrite($handle, "\n");
            }

            $pdo->exec('COMMIT');
            $started = false;
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        } catch (\Throwable $e) {
            if ($started) {
                try { $pdo->exec('ROLLBACK'); } catch (\Throwable) {}
            }
            throw $e;
        } finally {
            fclose($handle);
            // Restore Laravel's normal buffered-query behavior for this request.
            try { $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true); } catch (\Throwable) {}
        }
    }

    private function gzipFile(string $source, string $destination): void
    {
        $in = fopen($source, 'rb');
        $out = gzopen($destination, 'wb9');
        if ($in === false || $out === false) {
            if (is_resource($in)) fclose($in);
            if (is_resource($out)) gzclose($out);
            throw new \RuntimeException('Cannot compress backup file.');
        }

        try {
            while (! feof($in)) {
                $chunk = fread($in, 1024 * 1024);
                if ($chunk !== false && $chunk !== '') {
                    gzwrite($out, $chunk);
                }
            }
        } finally {
            fclose($in);
            gzclose($out);
        }
    }

    private function pruneOldBackups($disk): void
    {
        $files = collect($disk->files('backups'))
            ->filter(fn ($f) => preg_match('/^backups\/backup_[0-9_]+\.sql\.gz$/', $f))
            ->sortDesc()
            ->values();

        foreach ($files->slice(15) as $old) {
            $disk->delete($old);
        }
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    public function download(string $file)
    {
        $this->validName($file);
        $path = 'backups/' . $file;
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, $file, [
            'Content-Type' => 'application/gzip',
            'Content-Disposition' => 'attachment; filename="' . $file . '"',
        ]);
    }

    public function destroy(string $file)
    {
        $this->validName($file);
        Storage::disk('local')->delete('backups/' . $file);
        return back()->with('success', 'Backup سڕایەوە.');
    }
}
