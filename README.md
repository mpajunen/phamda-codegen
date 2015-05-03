# Phamda Code Generator

Code generator for the [Phamda](https://github.com/mpajunen/phamda) library.

- Generates curried functions from inner functions.
- Generates basic test cases based on the same inner functions.

## Usage

To add or modify a function in Phamda:

- Fork and clone the main repository.
- Clone this repository.
- Install dependencies: `composer install`.
- Make changes to the `src/Builder/InnerFunctions.php` file in the main library.
- Execute the generator script: `./generate.sh /path/to/phamda/directory`.
- Add a test data provider to `tests/BasicProvidersTrait.php` file in the main library if adding a new function.

[PHP CS Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) is also required.

## License

MIT license, see LICENSE file.
