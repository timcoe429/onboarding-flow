# Architectural Decisions - Docket Onboarding

This document records significant decisions made during the project, the context behind them, and their implications.

---

## Decision: Config-Driven Form Architecture
**Date**: Initial architecture design  
**Status**: ✅ Implemented and working well

### Context
Originally had three separate form implementations with significant code duplication. Needed a way to maintain three different website plans without tripling maintenance burden.

### Decision
Centralize all form configuration in `includes/forms/form-config.php`. This single file defines steps, fields, validation rules, and display logic for all three forms.

### Rationale
- Single source of truth reduces errors
- Adding new form types requires config changes, not code duplication
- Easier to maintain consistency across forms
- Allows business users to potentially edit config without developer intervention

### Consequences
- **Positive**: Dramatically reduced code duplication, easier to add new plans
- **Positive**: Bug fixes automatically apply to all forms
- **Negative**: Config file can become large (needs organization)
- **Tradeoff**: Less flexibility for truly unique form behaviors (acceptable for our use case)

### Related Files
- `includes/forms/form-config.php`
- `docs/FORM-ARCHITECTURE.md`

---

## Decision: Shared Steps Pattern
**Date**: Refactoring completed 2025  
**Status**: ✅ Implemented (only Fast Build step 5 remains unique)

### Context
All three forms share 90% of their steps. Originally had duplicate step files for each form type.

### Decision
Create shared step files in `includes/forms/shared/steps/` that all forms use. Form-specific behavior controlled by form-config.php, not separate files.

### Rationale
- Eliminates code duplication
- Ensures consistent UX across all forms
- Makes it obvious when new form-specific logic is being added
- Easier to update all forms at once

### Consequences
- **Positive**: Single step file serves all forms
- **Positive**: Bugs fixed once instead of three times
- **Negative**: Must ensure shared steps don't make assumptions about form type
- **Learning**: Only Fast Build step 5 actually needed to be unique

### Related Files
- `includes/forms/shared/steps/`
- `includes/forms/fast-build/steps/step-5-service-areas.php` (only unique step)
- `docs/SHARED-STEPS-REFACTORING.md`

---

## Decision: Unified JavaScript Approach
**Date**: JavaScript refactoring  
**Status**: ✅ Implemented

### Context
Had separate JavaScript files for each form type, causing maintenance burden and inconsistency.

### Decision
Single JavaScript file (`assets/docket-form-unified.js`) handles all three forms using data attributes and form-config-driven logic.

### Rationale
- Consistent client-side behavior across all forms
- Single place to fix bugs or add features
- Smaller total JavaScript payload
- Easier to understand overall form behavior

### Consequences
- **Positive**: One-third the JavaScript to maintain
- **Positive**: Consistent form validation and UX
- **Challenge**: Must handle form-specific logic gracefully
- **Solution**: Use data attributes and config to drive differences

### Related Files
- `assets/docket-form-unified.js`

---

## Decision: Remove Nonce Verification from Multi-Step Forms
**Date**: Recent  
**Status**: ✅ Implemented

### Context
WordPress nonce verification was causing user timeouts on longer forms. Users would start a form, pause, and return to find their session expired.

### Decision
Removed nonce verification from form submission. Multi-step complexity provides natural bot protection.

### Rationale
- Multi-step forms with field validation are inherently difficult for bots
- User experience more important than theoretical security benefit
- No evidence of bot submissions in production
- Nonces timing out caused real user frustration

### Consequences
- **Positive**: Users can complete forms at their own pace
- **Positive**: Eliminated timeout-related support tickets
- **Risk**: Slightly reduced CSRF protection (mitigated by form complexity)
- **Monitoring**: Watch for bot submissions (none seen yet)

---

## Decision: Comprehensive Cache Clearing During Multisite Operations
**Date**: Recent - WordPress cache fix  
**Status**: ✅ Implemented

### Context
WordPress cache issues during multisite context switching caused incorrect page slugs in cloned sites. Problem was sporadic and difficult to reproduce.

### Decision
Implement aggressive cache clearing immediately after every `switch_to_blog()` call:
- `wp_cache_flush()`
- `wp_cache_delete()`
- `wp_cache_switch_to_blog()`
- `clean_blog_cache()`

### Rationale
- WordPress core caching behavior during multisite context switching is complex
- Better to over-clear cache than have sporadic bugs
- Performance impact minimal compared to reliability improvement
- Same issues occurred in other cloning systems, suggesting core WordPress behavior

### Consequences
- **Positive**: Eliminated sporadic page slug issues
- **Positive**: More predictable multisite behavior
- **Negative**: Slight performance overhead (acceptable)
- **Learning**: WordPress cache issues often require comprehensive clearing, not targeted fixes

### Related Files
- Site cloning logic (wherever switch_to_blog() is used)

---

## Decision: Production Testing for Integration Issues
**Date**: Ongoing practice  
**Status**: ✅ Accepted practice

### Context
Some integrations (Trello, ChurnZero, site cloner API) require production credentials to function properly. Docker environment can't fully replicate these.

### Decision
Accept that certain features must be tested in production. Use feature flags and careful rollouts to minimize risk.

### Rationale
- Some integrations fundamentally require production environment
- Local Docker testing still valuable for non-integration code
- Tim prefers immediate results over lengthy staging processes
- Risk mitigated by careful testing and quick rollback ability

### Consequences
- **Positive**: Faster iteration on integration issues
- **Positive**: Testing with real data reveals actual problems
- **Risk**: Production changes can affect real users
- **Mitigation**: Test thoroughly in Docker first, use feature flags, monitor closely

---

## Decision: Cursor AI as Primary Development Tool
**Date**: Project standard  
**Status**: ✅ Standard practice

### Context
Needed efficient way to implement features while maintaining high code quality and consistency.

### Decision
Use Cursor AI in plan mode for all development. Claude creates prompts, Cursor generates plans, team reviews before implementation.

### Rationale
- Cursor excels at understanding codebase context
- Plan mode allows review before implementation
- Reduces time from idea to working code
- Maintains consistency through prompt-driven development

### Consequences
- **Positive**: Faster development cycles
- **Positive**: More consistent code patterns
- **Positive**: Better documentation through prompt process
- **Learning Curve**: Team had to learn effective prompt writing
- **Dependency**: Reliant on Cursor AI availability

### Workflow
1. Discuss need with Claude
2. Claude creates detailed Cursor prompt
3. Paste in Cursor plan mode
4. Review plan together
5. Cursor builds it
6. Test with composer test

---

## Decision: Separate Forms Site from Hosting Site
**Date**: Original architecture  
**Status**: ✅ Core architecture

### Context
Need to manage onboarding forms separately from the WordPress Multisite that hosts client sites.

### Decision
- **yourdocketonline.com**: Hosts onboarding forms and plugin
- **dockethosting5.com**: WordPress Multisite for cloning and hosting

### Rationale
- Separation of concerns (forms vs hosting)
- Easier to manage and scale independently
- Security isolation between form processing and client sites
- Allows different server configurations for each purpose

### Consequences
- **Positive**: Clear separation of responsibilities
- **Positive**: Easier to scale each independently
- **Complexity**: Requires API communication between servers
- **Challenge**: Some testing requires both environments

---

## Decision Patterns We Follow

### When to Add New Form-Specific Code
Ask these questions:
1. Can this be handled in form-config.php?
2. If not, can a shared step handle it with config driving behavior?
3. If truly unique, create form-specific file and document why

### When to Clear WordPress Cache
Be aggressive. When in doubt, clear more rather than less. Multisite context switching especially requires comprehensive clearing.

### When to Test in Production
- Integration features that need production credentials
- Issues that don't reproduce in Docker
- After thorough local testing of non-integration code

### When to Create New Documentation
- Any architectural decision that affects multiple files
- Patterns other developers should follow
- Solutions to non-obvious problems

---

## Decisions Under Consideration

### Should we build a form builder UI?
**Context**: Currently form-config.php requires developer knowledge to edit.

**Options**:
1. Build admin UI for form configuration
2. Keep config-driven approach with better documentation
3. Hybrid: UI for common changes, config for advanced

**Considerations**:
- How often do forms actually change?
- Who needs to make changes?
- Development time vs. benefit

**Status**: Deferred - current approach working well

---

### How to handle ChurnZero email tracking?
**Context**: Need to track email opens and clicks for customer success.

**Options**:
1. API-based tracking (current direction)
2. Email CC approach (didn't work)
3. Webhook-based tracking

**Considerations**:
- Reliability of each approach
- Development complexity
- Maintenance burden

**Status**: In progress - exploring API approach
