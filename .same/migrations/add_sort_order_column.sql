-- Migration: Add sort_order column for drag and drop sorting
-- Run this SQL on your database to enable drag and drop reordering

-- Add sort_order column to prg_pdf table
ALTER TABLE `prg_pdf`
ADD COLUMN `sort_order` INT DEFAULT 0 AFTER `dagid`;

-- Add sort_order column to program_oversikt table
ALTER TABLE `program_oversikt`
ADD COLUMN `sort_order` INT DEFAULT 0 AFTER `dagid`;

-- Create index for better performance on sorting queries
CREATE INDEX `idx_prg_pdf_sort` ON `prg_pdf` (`dagid`, `sort_order`);
CREATE INDEX `idx_program_oversikt_sort` ON `program_oversikt` (`dagid`, `sort_order`);

-- Initialize sort_order based on existing ID order (optional)
-- This sets the initial order based on the current ID sequence
UPDATE `prg_pdf` p1
SET `sort_order` = (
    SELECT COUNT(*)
    FROM (SELECT id, dagid FROM prg_pdf) p2
    WHERE p2.dagid = p1.dagid AND p2.id < p1.id
);

UPDATE `program_oversikt` p1
SET `sort_order` = (
    SELECT COUNT(*)
    FROM (SELECT id, dagid FROM program_oversikt) p2
    WHERE p2.dagid = p1.dagid AND p2.id < p1.id
);
