Fix summary (based on the supplied project archive):
- POS sends weighted quantity in kilograms (grams / 1000). SaleService now multiplies kilograms by price per kilogram, fixing the 15,000 vs 15 price mismatch.
- Sale ledger records the full sale amount as debt and records any amount received as a payment, so partial payment leaves the correct balance.
- Customer balance is derived from sale debt_amount (remaining debt), opening balance, later customer payments, and adjustments. This also makes old partial-payment sales visible in the debt list even if their historical ledger entries were inconsistent.
- No routes, migrations, environment files, or database data were changed.
After deploy, run: php artisan optimize:clear
Test with a NEW sale: total 15,000 / paid 15,000 => balance 0; total 15,000 / paid 7,500 => balance 7,500; total 15,000 / paid 0 => balance 15,000.
