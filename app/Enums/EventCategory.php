<?php

namespace App\Enums;

enum EventCategory: string
{
    case ACADEMIC = 'Academic';
    case SPORTS = 'Sports';
    case CULTURAL = 'Cultural';
    case WORKSHOP = 'Workshop';
    case SOCIAL = 'Social';
    case CAREER = 'Career';
    case TECHNOLOGY = 'Technology';

    /**
     * Get the human-readable label for the enum case.
     */
    public function label(): string
    {
        return match($this) {
            self::ACADEMIC => 'Academic & Education',
            self::SPORTS => 'Sports & Fitness',
            self::CULTURAL => 'Arts & Culture',
            self::WORKSHOP => 'Workshops & Training',
            self::SOCIAL => 'Social & Networking',
            self::CAREER => 'Career & Professional',
            self::TECHNOLOGY => 'Technology & IT',
        };
    }

    /**
     * Get all values as a simple array (for simple lists).
     * Returns: ['Academic', 'Sports', ...]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all options as an array [value => label].
     * Useful for populating HTML select inputs.
     * Returns: ['Academic' => 'Academic & Education', ...]
     */
    public static function options(): array
    {
        $options = [];
        
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
