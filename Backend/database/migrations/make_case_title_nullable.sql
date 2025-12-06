-- Make case_title column nullable since it's been removed from the frontend form
USE `scheduling_management_system`;

ALTER TABLE `legal_cases` 
MODIFY COLUMN `case_title` VARCHAR(255) NULL;
