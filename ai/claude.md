# Start Here

Before starting any work, read these files in order:

1. `/ai/context.md` - What this project is
2. `/ai/current_plan.md` - Where we are and what's next
3. `/ai/decisions.md` - Why we made certain choices

## Additional Reference Documentation

For detailed information, also reference:
- `.cursorrules` - Coding standards and project structure reference
- `docs/FORM-ARCHITECTURE.md` - How unified forms work
- `docs/SHARED-STEPS-REFACTORING.md` - Shared steps detail
- `docs/PROJECT-STRUCTURE.md` - File locations
- `docs/QUICK-REFERENCE.md` - Common tasks and SQL queries
- `docs/INTEGRATION-GUIDE.md` - Site cloner API

After reading, confirm what phase we're in and what the next task is.

## Architectural Principles

When proposing solutions, always consider maintainability and scalability alongside immediate functionality.

### Before Proposing a Solution, Ask:
- Is there a systemic way to solve this, or is it truly one-off?
- If this needs to happen in multiple places, can it be centralized?
- Will this decision make the codebase easier or harder to maintain?
- Does this create a clear pattern others can follow?
- How does this scale when we add more website plans or form types?

### Watch Out For:
- Solutions that require manual repetition for each new form type
- Hard-coded form-specific logic that should be config-driven
- Quick fixes that work now but don't scale to new plans
- Scattered logic that should be in form-config.php

### Prioritize:
- Config-driven solutions over hard-coded form logic
- Shared steps over duplicated code
- Clear patterns in form-config.php that guide correct usage
- Code organization that makes adding new form types easy
- Unified JavaScript that handles all forms consistently

### Think in Layers
- Form configuration (form-config.php) is the source of truth
- Shared steps handle common logic
- Form-specific overrides only when truly necessary
- Unified JS coordinates all client-side behavior

### Question the Obvious
The most direct solution isn't always the best solution. Take a moment to consider:
- Should this be in form-config.php instead of hard-coded?
- Can shared steps handle this instead of form-specific code?
- How would this look if we had 10 different form types?
- Does this belong in the unified JS or form-specific handlers?
