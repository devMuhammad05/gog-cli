<?php

namespace App\Commands;

use App\Services\GmailService;
use LaravelZero\Framework\Commands\Command;
use function Laravel\Prompts\{intro, outro, spin, table, text, note, error};

class WeekEmailsCommand extends Command
{
    protected $signature = 'gmail:week';
    protected $description = 'List emails from this week';

    public function handle(GmailService $gmailService)
    {
        intro('This Week\'s Emails');

        try {
            $emails = spin(fn() => $this->fetchEmails($gmailService), 'Fetching this week\'s emails...');

            if (empty($emails)) {
                note('No emails from this week found.');
                return;
            }

            // Prepare data for table
            $headers = ['#', 'From', 'Subject', 'Date'];
            $rows = [];
            $map = [];

            foreach ($emails as $index => $data) {
                $num = $index + 1;
                $rows[] = [
                    $num,
                    $data['from'],
                    $data['subject'],
                    $data['date']
                ];
                $map[$num] = $data['id'];
            }

            // Display table
            table($headers, $rows);

            // Ask for selection
            $selection = text(
                label: 'Enter # to read details',
                placeholder: 'e.g. 1',
                validate: fn($val) => isset($map[$val]) ? null : 'Please enter a valid number from the list.'
            );

            $this->showEmailDetails($gmailService, $map[$selection]);

            outro('Done');
        } catch (\Exception $e) {
            error("Error: " . $e->getMessage());
            return 1;
        }
    }

    private function fetchEmails(GmailService $gmailService): array
    {
        // Get the start of this week (Monday)
        $startOfWeek = date('Y/m/d', strtotime('monday this week'));
        $query = "after:$startOfWeek";
        
        $messages = $gmailService->listMessages(30, $query);

        if (empty($messages)) {
            return [];
        }

        $results = [];

        foreach ($messages as $msg) {
            $details = $gmailService->getMessage($msg->getId());
            $headers = collect($details->getPayload()->getHeaders());

            $subject = $headers->firstWhere('name', 'Subject')['value'] ?? '(No Subject)';
            $from = $headers->firstWhere('name', 'From')['value'] ?? '(Unknown)';
            $date = $headers->firstWhere('name', 'Date')['value'] ?? '';

            // Clean up From field
            $fromShort = str_replace('"', '', explode('<', $from)[0]);
            $fromShort = trim($fromShort) ?: $from;

            // Truncate subject for table
            $subjectShort = strlen($subject) > 50 ? substr($subject, 0, 47) . '...' : $subject;

            $results[] = [
                'id' => $msg->getId(),
                'from' => $fromShort,
                'subject' => $subjectShort,
                'date' => $date,
            ];
        }

        return $results;
    }

    private function showEmailDetails(GmailService $gmailService, string $id)
    {
        $details = spin(fn() => $gmailService->getMessage($id), 'Fetching email details...');

        $headers = collect($details->getPayload()->getHeaders());
        $subject = $headers->firstWhere('name', 'Subject')['value'] ?? '(No Subject)';
        $from = $headers->firstWhere('name', 'From')['value'] ?? '(Unknown)';
        $date = $headers->firstWhere('name', 'Date')['value'] ?? '';
        $snippet = $details->getSnippet();

        note(
            "From: $from\n" .
                "Subject: $subject\n" .
                "Date: $date\n\n" .
                "Snippet:\n$snippet"
        );
    }
}