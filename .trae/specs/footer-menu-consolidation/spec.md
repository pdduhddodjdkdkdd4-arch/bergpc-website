# Footer Menu Consolidation - Product Requirement Document

## Overview
- **Summary**: Move footer menu items (Our Firm, Business Litigation, Crypto Litigation) from separate sections (2, 3, 4) into section-1, alongside address and contact info, for all pages except index.html
- **Purpose**: Consolidate footer layout to display all information in a more compact area
- **Target Users**: Website visitors

## Goals
- Move site-footer-primary-section-2, 3, 4 menu content into section-1
- Maintain original HTML structure and CSS classes
- Only modify non-homepage pages

## Non-Goals (Out of Scope)
- Do not modify index.html or index.htm homepage
- Do not modify social media icons or email/phone display
- Do not change menu content or links

## Background & Context
- Current footer structure contains 4 separate sections
- section-1: logo, address, contact info (email and phone)
- section-2: Our Firm menu
- section-3: Business Litigation menu
- section-4: Crypto Litigation menu
- User wants to consolidate these menus into section-1

## Functional Requirements
- **FR-1**: Identify all pages to modify (exclude index.html and index.htm)
- **FR-2**: Extract menu HTML from section-2, 3, 4
- **FR-3**: Insert extracted menu HTML after footer-contact-info in section-1
- **FR-4**: Remove original section-2, 3, 4 containers

## Non-Functional Requirements
- **NFR-1**: Maintain original CSS classes and styles
- **NFR-2**: Maintain menu accessibility and functionality
- **NFR-3**: All modifications use Python script for batch processing

## Constraints
- **Technical**: Need to identify correct HTML structure
- **Dependencies**: None

## Assumptions
- All non-homepage pages have the same footer structure
- Menu HTML blocks can be accurately extracted

## Acceptance Criteria

### AC-1: Only non-homepage pages modified
- **Given**: All HTML pages
- **When**: Modification complete
- **Then**: index.html and index.htm unchanged, other pages modified
- **Verification**: programmatic

### AC-2: Menus moved to section-1
- **Given**: Modified pages
- **When**: Viewing footer
- **Then**: Our Firm, Business Litigation, Crypto Litigation menus display in section-1
- **Verification**: programmatic

### AC-3: Original section-2, 3, 4 deleted
- **Given**: Modified pages
- **When**: Viewing HTML structure
- **Then**: No independent section-2, 3, 4 containers exist
- **Verification**: programmatic

### AC-4: Layout remains functional
- **Given**: Modified pages
- **When**: Accessing website
- **Then**: All menu links work normally
- **Verification**: human-judgment

## Open Questions
- [ ] Should section-1 layout be adjusted to accommodate more content?
