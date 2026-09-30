<?php

namespace Database\Seeders;

use App\Models\MaintenanceReport;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MaintenanceReportSeeder extends Seeder
{
    public function run(): void
    {
        // Maintenance staff account used to log in
        $staff = User::firstOrNew(['email' => 'staff@school.test']);
        $staff->name     = 'Maintenance Staff';
        $staff->password = Hash::make('password123');
        $staff->role     = 'staff';
        $staff->save();

        $samples = [
            [
                'days_ago' => 6, 'reporter_name' => 'Maria Santos', 'reporter_role' => 'Teacher',
                'building' => 'Main Building', 'room' => 'Room 204', 'category' => 'Electrical',
                'title' => 'Ceiling lights keep flickering',
                'description' => 'Two of the four ceiling lights flicker during class and give students headaches. Started last Monday.',
                'priority' => 'high', 'status' => 'in_progress', 'assigned_to' => 'Mr. Reyes',
            ],
            [
                'days_ago' => 1, 'reporter_name' => 'Juan Dela Cruz', 'reporter_role' => 'Student',
                'building' => 'Science Hall', 'room' => 'Chemistry Lab', 'category' => 'Plumbing',
                'title' => 'Faucet leaking at station 3',
                'description' => 'The faucet at lab station 3 drips constantly and the floor below is getting wet and slippery.',
                'priority' => 'urgent', 'status' => 'pending', 'assigned_to' => null,
            ],
            [
                'days_ago' => 8, 'reporter_name' => 'Ana Villanueva', 'reporter_role' => 'Staff',
                'building' => 'Library', 'room' => 'Reading Area', 'category' => 'Furniture',
                'title' => 'Wobbly reading tables',
                'description' => 'Three tables near the window wobble when students lean on them. Legs may need tightening.',
                'priority' => 'medium', 'status' => 'pending', 'assigned_to' => null,
            ],
            [
                'days_ago' => 4, 'reporter_name' => 'Carlo Ramos', 'reporter_role' => 'Teacher',
                'building' => 'Computer Laboratory', 'room' => 'Lab 1', 'category' => 'Classroom equipment',
                'title' => 'Projector will not turn on',
                'description' => 'The ceiling projector shows a blinking red light and does not display anything from the teacher PC.',
                'priority' => 'high', 'status' => 'resolved', 'assigned_to' => 'IT Office',
                'resolved_at' => now()->subDays(2),
            ],
            [
                'days_ago' => 3, 'reporter_name' => 'Liza Mendoza', 'reporter_role' => 'Student',
                'building' => 'Cafeteria', 'room' => 'Dining Hall', 'category' => 'Cleanliness',
                'title' => 'Trash bins overflowing after lunch',
                'description' => 'The bins near the exit are full by the end of lunch break and the smell spreads through the hall.',
                'priority' => 'low', 'status' => 'in_progress', 'assigned_to' => 'Janitorial team',
            ],
            [
                'days_ago' => 0, 'reporter_name' => 'Ben Aquino', 'reporter_role' => 'Staff',
                'building' => 'Gymnasium', 'room' => 'Main Court', 'category' => 'Safety and security',
                'title' => 'Loose bolt on basketball backboard',
                'description' => 'One of the backboard bolts looks loose and the board shakes when the ring is used. Please check before the next game.',
                'priority' => 'urgent', 'status' => 'pending', 'assigned_to' => null,
            ],
            [
                'days_ago' => 5, 'reporter_name' => 'Grace Lim', 'reporter_role' => 'Parent',
                'building' => 'Grounds', 'room' => 'Front Gate Walkway', 'category' => 'Grounds',
                'title' => 'Cracked pavement near the gate',
                'description' => 'A raised crack in the walkway is a tripping hazard for younger students during drop-off time.',
                'priority' => 'medium', 'status' => 'resolved', 'assigned_to' => 'Mr. Reyes',
                'resolved_at' => now()->subDay(),
            ],
        ];

        foreach ($samples as $sample) {
            $daysAgo = $sample['days_ago'];
            unset($sample['days_ago']);

            $report = new MaintenanceReport($sample);
            $report->created_at = now()->subDays($daysAgo);
            $report->updated_at = $report->created_at;
            $report->save();
        }
    }
}
