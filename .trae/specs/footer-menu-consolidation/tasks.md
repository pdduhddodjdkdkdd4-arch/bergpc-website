# Footer Menu Consolidation - The Implementation Plan

## [x] Task 1: Analyze footer structure
- **Priority**: P0
- **Depends On**: None
- **Description**: 
  - Read footer area of lawyers/index.htm
  - Determine specific boundaries of section-1, 2, 3, 4
  - Extract menu HTML that needs to be moved
- **Acceptance Criteria Addressed**: AC-1
- **Test Requirements**:
  - programmatic TR-1.1: Identify correct HTML structure

## [x] Task 2: Create menu movement Python script
- **Priority**: P0
- **Depends On**: Task 1
- **Description**: 
  - Write Python script to process all HTML files
  - Exclude index.html and index.htm
  - Extract menu HTML from section-2, 3, 4
  - Insert menu HTML after footer-contact-info in section-1
  - Delete original section-2, 3, 4 containers
- **Acceptance Criteria Addressed**: AC-2, AC-3
- **Test Requirements**:
  - programmatic TR-2.1: Script can correctly identify and modify HTML structure

## [x] Task 3: Verify script on test file
- **Priority**: P0
- **Depends On**: Task 2
- **Description**: 
  - Select a test file (e.g., lawyers/index.htm)
  - Run script to verify modification effect
  - Check if HTML structure is correct
- **Acceptance Criteria Addressed**: AC-2, AC-3, AC-4
- **Test Requirements**:
  - programmatic TR-3.1: Menus correctly moved to section-1
  - programmatic TR-3.2: Original section-2, 3, 4 deleted

## [x] Task 4: Batch execute script
- **Priority**: P0
- **Depends On**: Task 3
- **Description**: 
  - Execute script on all non-homepage pages
  - Ensure index.html and index.htm unaffected
- **Acceptance Criteria Addressed**: AC-1, AC-2, AC-3
- **Test Requirements**:
  - programmatic TR-4.1: All target pages successfully modified

## [x] Task 5: Verify final results
- **Priority**: P1
- **Depends On**: Task 4
- **Description**: 
  - Check multiple pages to verify correct modifications
  - Confirm all menu links work properly
- **Acceptance Criteria Addressed**: AC-4
- **Test Requirements**:
  - human-judgment TR-5.1: Check visual effects and functionality
