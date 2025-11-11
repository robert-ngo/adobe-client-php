.PHONY: help install test test-coverage psalm lint fix clean

help: ## Display this help message
	@echo "Available targets:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2}'

install: ## Install dependencies
	composer install

update: ## Update dependencies
	composer update

test: ## Run tests
	vendor/bin/phpunit

test-coverage: ## Run tests with coverage report
	vendor/bin/phpunit --coverage-html coverage --coverage-text

test-filter: ## Run specific test (use TEST=ClassName)
	vendor/bin/phpunit --filter $(TEST)

psalm: ## Run static analysis with Psalm
	vendor/bin/psalm

psalm-info: ## Run Psalm with detailed information
	vendor/bin/psalm --show-info=true --stats

psalm-baseline: ## Generate Psalm baseline
	vendor/bin/psalm --set-baseline=psalm-baseline.xml

lint: ## Check code style (dry-run)
	vendor/bin/php-cs-fixer fix --dry-run --diff

fix: ## Fix code style issues
	vendor/bin/php-cs-fixer fix

clean: ## Clean generated files
	rm -rf coverage/
	rm -f .phpunit.result.cache
	rm -rf vendor/

# DDEV specific targets
ddev-install: ## Install dependencies using DDEV
	ddev composer install

ddev-test: ## Run tests using DDEV
	ddev phpunit

ddev-psalm: ## Run Psalm using DDEV
	ddev exec vendor/bin/psalm

ddev-shell: ## Open DDEV shell
	ddev ssh

# CI simulation
ci: install psalm test ## Run CI pipeline locally

# Development helpers
watch-test: ## Watch tests (requires entr: brew install entr)
	find src tests -name '*.php' | entr -c make test

# Quality gates
quality: lint psalm test ## Run all quality checks

# Release helpers
version: ## Show current version from composer.json
	@grep '"version"' composer.json | awk -F'"' '{print $$4}'
