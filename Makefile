.PHONY: cs help it test

it: cs test ## Runs the cs and test targets

cs: vendor ## Fixes code style issues with php-cs-fixer
	mkdir -p .build/php-cs-fixer
	vendor/bin/php-cs-fixer fix --config=.php_cs --diff --diff-format=udiff --verbose

help: ## Displays this list of targets with descriptions
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}'

test: vendor ## Runs auto-review, unit, and integration tests with phpunit
	mkdir -p .build/phpunit
	vendor/bin/phpunit --configuration=phpunit.xml

vendor: composer.json composer.lock
	composer validate --strict
	composer install --no-interaction --no-progress --no-suggest
	composer normalize