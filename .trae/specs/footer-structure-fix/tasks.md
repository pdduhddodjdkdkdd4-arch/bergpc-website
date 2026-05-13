# Footer Structure Fix - Implementation Plan

## [ ] Task 1: Restore lawyers/index.htm from backup
- **Priority**: P0
- **Depends On**: None
- **Description**: Restore lawyers/index.htm from lawyers/index_backup.htm to have correct reference structure
- **Acceptance Criteria Addressed**: AC-1
- **Test Requirements**:
  - programmatic TR-1.1: Verify backup is restored correctly

## [ ] Task 2: Write script to fix structure for all files
- **Priority**: P0
- **Depends On**: Task 1
- **Description**: Create a Python script that:
  - Takes the correct structure from restored lawyers/index.htm
  - For all other non-homepage pages, ensures section-2, 3, 4 are siblings
  - Maintains all content including email/phone in section-1
- **Acceptance Criteria Addressed**: AC-1, AC-2
- **Test Requirements**:
  - programmatic TR-2.1: Script correctly identifies and fixes structure

## [ ] Task 3: Test on one file first
- **Priority**: P0
- **Depends On**: Task 2
- **Description**: Test script on one file and verify structure is correct
- **Acceptance Criteria Addressed**: AC-1, AC-2
- **Test Requirements**:
  - programmatic TR-3.1: Structure is fixed
  - human-judgment TR-3.2: Content looks correct

## [ ] Task 4: Apply to all files
- **Priority**: P0
- **Depends On**: Task 3
- **Description**: Run script on all non-homepage files
- **Acceptance Criteria Addressed**: AC-1, AC-2
- **Test Requirements**:
  - programmatic TR-4.1: All files processed

## [ ] Task 5: Verify all files
- **Priority**: P1
- **Depends On**: Task 4
- **Description**: Check multiple files to ensure structure is correct
- **Acceptance Criteria Addressed**: AC-1, AC-2
- **Test Requirements**:
  - human-judgment TR-5.1: Verify structure visually
