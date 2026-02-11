# Current Plan - Docket Onboarding

**Last Updated**: February 11, 2026

## Where We Are Now

### Recently Completed
- ✅ Fixed WordPress cache issues during multisite context switching
  - Implemented comprehensive cache clearing after switch_to_blog() calls
  - Added wp_cache_flush(), wp_cache_delete(), wp_cache_switch_to_blog(), clean_blog_cache()
  - Resolved incorrect page slugs in cloned sites
- ✅ Restructured client dashboard Trello card tracking
  - Separated "Pre Launch" from "Ready for Launch" status
  - Improved progress visualization for clients
- ✅ Unified form architecture implementation
  - Consolidated duplicate step files into shared directory
  - Reduced code duplication by ~54%
  - Standardized field names across all forms
  - Single JavaScript file handles all forms

### Currently Working On
- 🔄 ChurnZero email tracking integration
  - Exploring API-based solutions
  - Initial email CC approach didn't work as expected
  - Need to implement proper event tracking for email opens/clicks

### Known Issues
- ⚠️ Legacy plugin (docket-automated-site-creator) needs removal during cleanup
- ⚠️ Some integrations require production credentials to test properly

## What's Next

### Immediate Priorities (This Sprint)
1. **Complete ChurnZero Email Integration**
   - Implement API-based email tracking
   - Test event logging for key milestones
   - Document integration pattern

2. **Client Dashboard Enhancements**
   - Finalize Trello card movement tracking
   - Add visual progress indicators
   - Test across all three form types

### Short-Term Goals (Next 2-4 Weeks)
1. **Code Cleanup**
   - Remove docket-automated-site-creator plugin
   - Consolidate any remaining duplicated form logic
   - Update documentation to reflect current architecture

2. **Testing Infrastructure**
   - Expand composer test coverage
   - Document local Docker testing procedures
   - Create integration testing guide

3. **Performance Optimization**
   - Review and optimize multisite cache handling
   - Analyze form submission performance
   - Optimize API calls to cloning server

### Long-Term Vision (Next Quarter)
1. **Scalability Improvements**
   - Make it easier to add new form types/plans
   - Further consolidate form-config.php patterns
   - Build form builder UI for non-technical updates

2. **Enhanced Monitoring**
   - Better error tracking and logging
   - Integration health monitoring
   - Customer success metric dashboards

3. **Documentation**
   - Video tutorials for team onboarding
   - API documentation for external developers
   - Troubleshooting guides for common issues

## Development Workflow

### Standard Process
1. Tim brings issue/feature to Claude
2. Claude creates detailed Cursor prompt
3. Tim pastes in Cursor plan mode
4. Review Cursor's generated plan together
5. Tim has Cursor build it
6. Test with `composer test`
7. Deploy and monitor

### Testing Checklist
- [ ] Run `composer test` locally
- [ ] Test in Docker (localhost:8080) when possible
- [ ] Verify in production (some integrations require this)
- [ ] Check all three form types if change affects shared code
- [ ] Validate Trello integration
- [ ] Confirm ChurnZero events fire correctly

## Decisions Pending
- [ ] Best approach for ChurnZero email tracking (API vs webhook)
- [ ] Timeline for legacy plugin removal
- [ ] Whether to build form builder UI or keep config-driven approach
