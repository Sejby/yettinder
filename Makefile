.PHONY: tests stan pr

tests:
	php bin/phpunit --testdox

stan:
	vendor/bin/phpstan analyse --memory-limit=512M

pr: stan tests
