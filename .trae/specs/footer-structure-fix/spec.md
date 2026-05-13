# Footer Menu Structure Fix - Product Requirement Document

## Overview
- **Summary**: Fix the footer structure where site-footer-primary-section-2, 3, 4 were incorrectly inserted inside section-1 instead of being its siblings.
- **Purpose**: Ensure the footer sections are properly structured as siblings.
- **Target Users**: Website visitors

## Goals
- Restore proper footer structure with section-1, 2, 3, 4 as sibling elements
- Maintain all footer content (email, phone, menus)
- Ensure correct indentation and styling

## Non-Goals (Out of Scope)
- Do not modify content, just the structure
- Do not modify index.html homepage

## Background & Context
- Original structure: section-1, 2, 3, 4 are all siblings inside ast-builder-footer-grid-columns
- Current problem: section-2, 3, 4 were mistakenly inserted inside section-1
- Need to restore to original sibling structure

## Functional Requirements
- **FR-1**: Restore lawyers/index.htm from backup first to use as reference
- **FR-2**: For all non-homepage pages, ensure section-2, 3, 4 are siblings of section-1
- **FR-3**: Maintain all existing content including email, phone, and menus

## Non-Functional Requirements
- **NFR-1**: Correct indentation matching original structure
- **NFR-2**: All links and content preserved

## Constraints
- **Technical**: Must maintain proper HTML structure

## Assumptions
- lawyers/index_backup.htm has the correct structure
- Other pages had the same issue

## Acceptance Criteria

### AC-1: Sibling structure restored
- **Given**: All non-homepage pages
- **When**: Structure is fixed
- **Then**: section-1, 2, 3, 4 are all siblings
- **Verification**: programmatic

### AC-2: All content preserved
- **Given**: Fixed pages
- **When**: Check content
- **Then**: Email, phone, and all menu items are present
- **Verification**: programmatic

## Open Questions
- None
