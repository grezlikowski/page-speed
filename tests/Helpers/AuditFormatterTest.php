<?php

use Grezlikowski\PageSpeed\Helpers\AuditFormatter;

describe('formatBytes', function () {
    it('formats zero bytes', function () {
        expect(AuditFormatter::formatBytes(0))->toBe('0 B');
    });

    it('formats bytes under 1 KiB', function () {
        expect(AuditFormatter::formatBytes(512))->toBe('512 B');
    });

    it('formats kilobytes', function () {
        expect(AuditFormatter::formatBytes(2048))->toBe('2.0 KiB');
    });

    it('formats megabytes', function () {
        expect(AuditFormatter::formatBytes(1048576))->toBe('1.0 MiB');
    });

    it('handles string input', function () {
        expect(AuditFormatter::formatBytes('1024'))->toBe('1.0 KiB');
    });
});

describe('formatMs', function () {
    it('formats milliseconds under 1 second', function () {
        expect(AuditFormatter::formatMs(500))->toBe('500 ms');
    });

    it('formats milliseconds as seconds when over 1000', function () {
        expect(AuditFormatter::formatMs(2500))->toBe('2.5 s');
    });

    it('formats exactly 1000ms as seconds', function () {
        expect(AuditFormatter::formatMs(1000))->toBe('1.0 s');
    });

    it('handles zero', function () {
        expect(AuditFormatter::formatMs(0))->toBe('0 ms');
    });
});

describe('formatCellValue', function () {
    it('returns dash for null value', function () {
        expect(AuditFormatter::formatCellValue(null, 'text'))->toBe('—');
    });

    it('formats bytes type', function () {
        expect(AuditFormatter::formatCellValue(2048, 'bytes'))->toBe('2.0 KiB');
    });

    it('formats timespanMs type', function () {
        expect(AuditFormatter::formatCellValue(500, 'timespanMs'))->toBe('500 ms');
    });

    it('formats ms type', function () {
        expect(AuditFormatter::formatCellValue(1500, 'ms'))->toBe('1.5 s');
    });

    it('formats numeric type', function () {
        expect(AuditFormatter::formatCellValue(12345.6, 'numeric'))->toBe('12,346');
    });

    it('formats array with nodeLabel', function () {
        expect(AuditFormatter::formatCellValue(['nodeLabel' => 'My Image'], 'node'))->toBe('My Image');
    });

    it('formats array with snippet fallback', function () {
        expect(AuditFormatter::formatCellValue(['snippet' => '<img src="...">'], 'node'))->toBe('<img src="...">');
    });

    it('formats string value as-is for text type', function () {
        expect(AuditFormatter::formatCellValue('hello', 'text'))->toBe('hello');
    });
});
