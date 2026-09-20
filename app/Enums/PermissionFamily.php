<?php

namespace App\Enums;

enum PermissionFamily: string
{
    case Dashboard = 'dashboard';

    case Students = 'students';

    case Teachers = 'teachers';

    case Parents = 'parents';

    case Classes = 'classes';

    case Subjects = 'subjects';

    case Sessions = 'sessions';

    case Attendance = 'attendance';

    case Exams = 'exams';

    case Results = 'results';

    case Grades = 'grades';

    case Fees = 'fees';

    case Invoices = 'invoices';

    case Payments = 'payments';

    case Notices = 'notices';

    case Events = 'events';

    case Library = 'library';

    case Transport = 'transport';

    case Users = 'users';

    case Roles = 'roles';

    case Settings = 'settings';

    case Reports = 'reports';

    /**
     * @return string[]
     */
    public function fullCrud(): array
    {
        return [
            $this->value.'.view',
            $this->value.'.create',
            $this->value.'.update',
            $this->value.'.delete',
        ];
    }

    public function view(): string
    {
        return $this->value.'.view';
    }

    public function manage(): string
    {
        return $this->value.'.manage';
    }
}