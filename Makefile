# Makefile for common development tasks

.PHONY: help install test test-watch test-coverage clean

help: ## Show this help message
	@echo "Available commands:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

install: ## Install dependencies
	composer install

test: ## Run tests
	composer test

test-watch: ## Run tests with verbose output
	composer test:watch

test-coverage: ## Generate test coverage report
	composer test:coverage
	@echo "Coverage report generated in coverage/ directory"

clean: ## Clean generated files
	rm -rf vendor/
	rm -rf coverage/
	rm -rf .phpunit.result.cache
	find . -name "*.log" -type f -delete

setup: install ## Initial setup
	@echo "✅ Setup complete! Run 'make test' to verify."

