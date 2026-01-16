<?php

namespace App\Enums;

enum EmployeeLifecycleEvent: string
{
    case AdUserCreated          = 'ad_user_created';
    case PersonalNumberAssigned = 'personal_number_assigned';
    case MutationCreated        = 'mutation_created';
    case AdUserChange           = 'ad_user_change';
    case TerminationRegistered  = 'termination_registered';
    case AdUserDisabled         = 'ad_user_disabled';
    case AdUserDeleted          = 'ad_user_deleted';

    public function label(): string
    {
        return match ($this) {
            self::AdUserCreated          => 'AD-Benutzer erstellt',
            self::PersonalNumberAssigned => 'Personalnummer zugewiesen',
            self::MutationCreated        => 'Mutation erstellt',
            self::AdUserChange           => 'AD-Benutzer geändert',
            self::TerminationRegistered  => 'Kündigung/Austritt erfasst',
            self::AdUserDisabled         => 'AD-Benutzer deaktiviert',
            self::AdUserDeleted          => 'AD-Benutzer gelöscht',
        };
    }
}
