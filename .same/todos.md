# RL Admin - Improvement Todos

## Current Work: Drag and Drop Sorting (COMPLETED)
- [x] Created sortable-list.js for jQuery UI sortable
- [x] Created sortable.css for styling
- [x] Created api/reorder_program.php endpoint
- [x] Updated program_oversikt.php with sortable lists
- [x] Updated prg_pdf.php to use sortable list structure
- [x] Added 'program' field to allowed fields whitelist
- [x] Fixed del_prg_pdf.php to delete from correct table
- [x] Created SQL migration for sort_order column
- [x] Added "Add new program" functionality to prg_pdf.php
- [x] Created api/add_prg_pdf.php endpoint
- [x] Created api/add_program.php endpoint (generic)
- [x] Added "Add new program" functionality to program_oversikt.php
- [x] Added enter key handler for editable content
- [x] Improved sortable.css with add button styling and mobile improvements

### Database Migration Required
Run the SQL in `.same/migrations/add_sort_order_column.sql` to add the `sort_order` column to both `prg_pdf` and `program_oversikt` tables. Without this, drag and drop order will not persist.

## PDF Export Features
- [x] print_pdf.php - Exports prg_pdf table (next week's program) with sorting
- [x] print_program_oversikt.php - Exports program_oversikt table (current published program) with sorting
- [x] Both PDF files refactored to use PDO prepared statements (security)
- [x] Removed mysqli queries, now using DB class
- [x] Added htmlspecialchars() for XSS protection in output
- Both PDFs use the same layout and formatting
- "Lag PDF" button available on both program pages

## Drag and Drop Features Summary
- **Drag handles**: Click and drag the handle icon to reorder items
- **Inline editing**: Click on time or program text to edit directly
- **Add new items**: "Legg til nytt program" button opens modal to add new entries
- **Delete items**: Click trash icon with confirmation dialog
- **Auto-save**: All changes are saved automatically via AJAX
- **Toast notifications**: Visual feedback for all operations
- **Mobile support**: Touch-friendly with responsive design

## Analysis Status
- [x] Clone repository
- [x] Analyze project structure
- [x] Review security practices
- [x] Create improvement suggestions document

## Security Improvements (Critical)
- [x] Fix SQL injection in `add_user_post.php` - DONE
- [ ] Move database credentials to environment variables
- [x] Upgrade password hashing to `password_hash()` - DONE
- [x] Add authentication to API endpoints - DONE
- [ ] Disable error display for production (configurable in init.php)
- [x] Add CSRF protection to all forms - DONE

## Authentication System Overhaul (COMPLETED)
- [x] Created Auth class (`classes/Auth.php`) with centralized authentication
- [x] Updated Token class with secure CSRF protection
- [x] Updated core/init.php to use new Auth system
- [x] Created proper logout.php page
- [x] Updated login (index.php) with CSRF protection
- [x] Fixed logout link in nav.php

## Pages Updated with Auth::requireLogin() (COMPLETED - 47 files)
All main admin pages, edit pages, form processing, delete endpoints, and API endpoints now require authentication.

## Traffic Analytics (NEW)
- [x] Created website_traffic database table migration
- [x] Created api/traffic.php with endpoints for recording and retrieving traffic data
- [x] Added traffic charts to dashboard.php (weekly and monthly views)
- [x] Created traffic_analytics.php with comprehensive analytics
- [x] Added navigation link to traffic analytics page
- [x] Features: hourly, daily, weekly, monthly views
- [x] Device and browser statistics with pie charts
- [x] Top pages table with visit counts
- [x] Hourly heatmap for last 24 hours
- [x] Created tracker.js - lightweight tracking script for public websites
- [x] Created traffic_settings.php - embed code and configuration page
- [x] Added real-time auto-refresh (every 30 seconds) to dashboard and analytics
- [x] Added live indicator with pulse animation
- [x] Added pause/resume auto-refresh controls
- [x] Added manual refresh button

### Database Migration Required
Run the SQL in `.same/migrations/create_website_traffic_table.sql` to create the traffic tracking tables.

## Remaining Tasks
- [ ] Move database credentials to environment variables (.env file)
- [ ] Add user profile management page
- [ ] Add admin dashboard for user permissions
- [ ] Create README.md with installation instructions
- [ ] Add rate limiting to login endpoint
- [ ] Add password strength requirements
- [ ] Implement session timeout configuration

## Summary
- **47+ files** now use Auth::requireLogin() or Auth::requireApiAuth()
- **12+ SQL injection vulnerabilities** fixed
- All main admin pages are now protected
- Drag and drop sorting implemented for program lists
- Add new program functionality on both program pages
