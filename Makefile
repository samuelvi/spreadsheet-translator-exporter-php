
.PHONY: install update rector rector-dry-run test test-coverage test-verbose clean help

install:
	composer install

update:
	composer update

rector:
	vendor/bin/rector process src

rector-dry-run:
	vendor/bin/rector process src --dry-run

test:
	vendor/bin/phpunit

test-verbose:
	vendor/bin/phpunit --testdox

test-coverage:
	vendor/bin/phpunit --coverage-html coverage/html --coverage-text

test-coverage-clover:
	vendor/bin/phpunit --coverage-clover coverage/clover.xml

clean:
	rm -rf coverage/ .phpunit.cache vendor/

help:
	@echo "Available targets:"
	@echo "  install            - Install dependencies via Composer"
	@echo "  update             - Update dependencies via Composer"
	@echo "  rector             - Run Rector to refactor code"
	@echo "  rector-dry-run     - Run Rector in dry-run mode"
	@echo "  test               - Run PHPUnit tests"
	@echo "  test-verbose       - Run tests with detailed output"
	@echo "  test-coverage      - Run tests with HTML coverage report"
	@echo "  test-coverage-clover - Run tests with Clover XML coverage"
	@echo "  clean              - Remove generated files (coverage, cache, vendor)"
	@echo "  help               - Show this help message"
