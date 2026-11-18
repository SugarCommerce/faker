# Faker

Fake data for seeding a WordPress shop while you build it, and real demo
files for it to sell.

## What it does

A plugin is easier to build against a hundred orders than against none, and
screenshots of an empty table sell nothing. Filling a site by hand takes an
afternoon, and `'Test Customer 1'` shows every layout bug except the ones
real data has.

This generates names, emails, prices, dates and the rest, weighted so the
spread looks like a real store rather than a uniform random one. And a seed
that uploads text files named `.wav` and `.pdf` gives every player and
preview nothing to show, so `Media` makes the real thing in code: a chord,
artwork, a one-page PDF, a ZIP of stems -- the same bytes on every run.

It is a development tool: require it with `--dev`, and keep it out of what
ships.

## Features

* Generate names, emails, phone numbers and companies that look real
* Generate prices in minor units, so nothing has to be converted afterwards
* Pick a value with weights, so most orders are complete and a few failed
* Get a date in the past, or between two points, for a believable history
* Pull a real id from the site: a user, post, attachment or term that exists
* Seed the generator, so a run gives the same data and the same screenshots again
* Make real WAV, PNG, PDF and ZIP files, byte-identical on every run

## Installation

```bash
composer require --dev sugarcommerce/faker
```

## Quick start

Seeding a customer:

```php
use SugarCommerce\Faker\Fake;

$customer = array(
	'name'         => Fake::name(),
	'email'        => Fake::email(),
	'country'      => Fake::country(),
	'total_spent'  => Fake::price(),
	'status'       => Fake::weighted( array(
		'active'   => 60,
		'pending'  => 20,
		'inactive' => 20,
	) ),
	'date_created' => Fake::past_date(),
);
```

The weights are the useful part: a seeded table where every status is
equally likely does not look like anything, and hides the layout problems a
realistic spread shows up.

## What it makes

| Call | Gives |
|---|---|
| `first_name()`, `last_name()`, `name()` | from an international list |
| `email( $first = null, $last = null )` | built from the name, reduced to what an address may hold |
| `phone()`, `company()` | `+44 207-555-0199`, `Okafor & Associates` |
| `price( $min = 100, $max = 500000 )` | minor units: 4999 is 49.99 |
| `sku()`, `product_name()` | `PRD-48213`, `Premium Toolkit` |
| `country()` | a two-letter code |
| `date_between( $start, $end, $format )`, `past_date( $max_days, $format )` | `Y-m-d H:i:s`, UTC |
| `sentence( $words )`, `paragraph( $sentences )` | lorem-free filler |
| `user_id( $role )`, `post_id( $type, $status )`, `attachment_id( $mime )`, `term_id( $taxonomy )` | a real id from the site, or null when there is none |
| `number( $min, $max )`, `boolean( $chance )`, `random_element( $items )`, `weighted( $weights )` | the building blocks |
| `many( $generator, $count )` | `$count` of anything |

`weighted()` ignores weights that are not positive numbers, and returns null
when none is. `number()` takes its bounds either way round.

## The same data again

```php
Fake::seed( 2026 );   // every value after this repeats on every run
Fake::seed( 0 );      // back to random
```

## Demo media

```php
use SugarCommerce\Faker\Media;

$media = new Media(
	note: 'Acme demo file -- not a real product.',   // under each PDF's heading
	creator: 'Acme seed',                             // the PDF's Creator
	placeholder: "Acme seed file\n{name}\n",          // any other extension, and each ZIP's README
);

$bytes = $media->for( 'Midnight Drive - Master.wav' );
```

| Name | Bytes |
|---|---|
| `*.wav` | Three seconds of 16-bit mono at 22,050 Hz: a chord whose root comes from the name, faded in and out; pulsed when the name contains "beat". |
| `*.png` | 600 × 600 artwork in the name's colours, with the name across it. Needs GD. |
| `*.pdf` | One page headed with the name: licence terms when it contains "licence", a short guide otherwise. |
| `*.zip` | A folder named for the archive: three one-second stems and a README. |
| anything else | The placeholder, with `{name}` filled in. |

The same name gives the same bytes on every run -- the PDF's creation date
and the ZIP's timestamps are fixed (`Media::MADE`, 2026-01-01, or `made:`),
and nothing is random -- so a seed that stores files by their digest writes
the same keys over the same objects instead of leaving a new set behind each
time. Each kind is there on its own too: `Media::wav( 440.0, 1.0, beat:
true )`, `Media::png( 'Cover', 300 )`, `$media->pdf( $title, $paragraphs )`,
`$media->zip( array( 'a.txt' => 'A' ) )`, `$media->text( $name )`.

PDFs are written by sugarcommerce/pdf and ZIPs by sugarcommerce/storage's
`ZipStream`.

## Moving from arraypress/wp-faker

| Was | Now |
|---|---|
| `arraypress/wp-faker` | `sugarcommerce/faker` |
| `ArrayPress\Faker\Fake` | `SugarCommerce\Faker\Fake` -- every method unchanged |
| `ArrayPress\Faker\Data` | `SugarCommerce\Faker\Data` -- every list unchanged |

New: `Fake::seed()`, and `Media` -- the retired `sugarcommerce/dev-tools`
package's `DemoMedia`, on the WordPress family's PDF and ZIP writers. Its
`docblocks` and `smoke` commands check a codebase rather than make data, and
are not here.

## Requirements

* PHP 8.3 or later
* WordPress 7.1 or later, for the ids from the site
* GD, for PNGs

## License

GPL-2.0-or-later
