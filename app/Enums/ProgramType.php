<?php

namespace App\Enums;

enum ProgramType: string
{
    case BCS = 'BCS';
    case BIT = 'BIT';
    case BSE = 'BSE';
    case BDS = 'BDS';
    case BCY = 'BCY';
    case BIS = 'BIS';
    case BEEE = 'BEEE';
    case BCHE = 'BCHE';
    case BCIV = 'BCIV';
    case BME = 'BME';
    case BBA = 'BBA';
    case BACC = 'BACC';
    case BFIN = 'BFIN';
    case BMM = 'BMM';
    case BIBM = 'BIBM';
    case BSCM = 'BSCM';
    case BSCP = 'BSCP';
    case BSCC = 'BSCC';
    case BSCB = 'BSCB';
    case BENG = 'BENG';
    case BCOMM = 'BCOMM';
    case BPSY = 'BPSY';
    case DIP = 'DIP';
    case FOUND = 'FOUND';
    case OTH = 'OTH';

    /**
     * Get the human-readable label for the enum case.
     */
    public function label(): string
    {
        return match($this) {
            self::BCS => 'Bachelor of Computer Science',
            self::BIT => 'Bachelor of Information Technology',
            self::BSE => 'Bachelor of Software Engineering',
            self::BDS => 'Bachelor of Data Science',
            self::BCY => 'Bachelor of Cyber Security',
            self::BIS => 'Bachelor of Information Systems',
            self::BEEE => 'Bachelor of Electrical and Electronic Engineering',
            self::BCHE => 'Bachelor of Chemical Engineering',
            self::BCIV => 'Bachelor of Civil Engineering',
            self::BME => 'Bachelor of Mechanical Engineering',
            self::BBA => 'Bachelor of Business Administration',
            self::BACC => 'Bachelor of Accounting',
            self::BFIN => 'Bachelor of Finance',
            self::BMM => 'Bachelor of Marketing Management',
            self::BIBM => 'Bachelor of International Business Management',
            self::BSCM => 'Bachelor of Science (Mathematics)',
            self::BSCP => 'Bachelor of Science (Physics)',
            self::BSCC => 'Bachelor of Science (Chemistry)',
            self::BSCB => 'Bachelor of Science (Biology)',
            self::BENG => 'Bachelor of Arts (English Language)',
            self::BCOMM => 'Bachelor of Communication',
            self::BPSY => 'Bachelor of Psychology',
            self::DIP => 'Diploma Programme',
            self::FOUND => 'Foundation Programme',
            self::OTH => 'Other',
        };
    }

    /**
     * Get all options as an array [value => label].
     * Useful for populating HTML select inputs.
     * 
     * @return array<string, string>
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

