<?php

namespace App\Services;

use App\Models\Task;
use Carbon\Carbon;

/**
 * Task Parser Service
 * 
 * Parses natural language input to extract task details.
 * Handles various formats like "meeting tomorrow at 2pm", "call client next week", etc.
 */
class TaskParserService
{
    private $taskKeywords = [
        'meeting', 'call', 'appointment', 'remind', 'task', 'todo', 'schedule',
        'visit', 'buy', 'get', 'pick up', 'drop off', 'submit', 'complete',
        'finish', 'send', 'email', 'text', 'message', 'work on', 'project'
    ];

    private $timeKeywords = [
        'morning' => '09:00',
        'afternoon' => '14:00', 
        'evening' => '18:00',
        'night' => '20:00',
        'noon' => '12:00',
        'midnight' => '00:00'
    ];

    private $priorityKeywords = [
        'urgent' => 'high',
        'important' => 'high',
        'asap' => 'high',
        'critical' => 'high',
        'later' => 'low',
        'sometime' => 'low',
        'eventually' => 'low'
    ];

    public function parseTask(string $message): array
    {
        $message = strtolower(trim($message));
        
        // Extract task title/description
        $title = $this->extractTitle($message);
        
        // Extract date
        $dueDate = $this->extractDate($message);
        
        // Extract time
        $dueTime = $this->extractTime($message);
        
        // Combine date and time
        $fullDueDate = $this->combineDateAndTime($dueDate, $dueTime);
        
        // Extract priority
        $priority = $this->extractPriority($message);
        
        // Calculate confidence
        $confidence = $this->calculateConfidence($message, $title, $dueDate);

        return [
            'title' => $title,
            'description' => $this->generateDescription($message),
            'due_date' => $fullDueDate ? $fullDueDate->format('Y-m-d H:i:s') : null,
            'priority' => $priority,
            'status' => 'pending',
            'created_via_ai' => true,
            'ai_raw_input' => $message,
            'confidence' => $confidence,
            'tags' => $this->extractTags($message)
        ];
    }

    private function extractTitle(string $message): string
    {
        // Remove time indicators
        $title = preg_replace('/\b(at \d{1,2}(:\d{2})?\s*(am|pm)?|tomorrow|today|next week|next month)\b/i', '', $message);
        
        // Remove priority indicators
        $title = preg_replace('/\b(urgent|important|asap|critical|later|sometime|eventually)\b/i', '', $title);
        
        // Remove common prefixes
        $title = preg_replace('/^(i have|i need to|i should|i must|remind me to|schedule|task:)/i', '', $title);
        
        // Clean up and capitalize
        $title = trim($title);
        $title = ucfirst($title);
        
        return $title ?: 'New Task';
    }

    private function extractDate(string $message): ?Carbon
    {
        $now = Carbon::now();
        
        // Today
        if (preg_match('/\btoday\b/', $message)) {
            return $now->copy();
        }
        
        // Tomorrow
        if (preg_match('/\btomorrow\b/', $message)) {
            return $now->copy()->addDay();
        }
        
        // Next week
        if (preg_match('/\bnext week\b/', $message)) {
            return $now->copy()->addWeek()->startOfWeek();
        }
        
        // Next month
        if (preg_match('/\bnext month\b/', $message)) {
            return $now->copy()->addMonth()->startOfMonth();
        }
        
        // Specific day names (this monday, friday, etc.)
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        foreach ($days as $day) {
            if (preg_match('/\b(this\s+|next\s+)?' . $day . '\b/', $message, $matches)) {
                $targetDay = $now->copy()->next($day);
                if (isset($matches[1]) && trim($matches[1]) === 'next') {
                    $targetDay->addWeek();
                }
                return $targetDay;
            }
        }
        
        // Specific date format (MM/DD, DD/MM, etc.)
        if (preg_match('/\b(\d{1,2})\/(\d{1,2})(?:\/(\d{2,4}))?\b/', $message, $matches)) {
            $month = (int)$matches[1];
            $day = (int)$matches[2];
            $year = isset($matches[3]) ? (int)$matches[3] : $now->year;
            
            if ($year < 100) {
                $year += 2000;
            }
            
            try {
                return Carbon::create($year, $month, $day);
            } catch (\Exception $e) {
                // Invalid date, return null
            }
        }
        
        return null;
    }

    private function extractTime(string $message): ?string
    {
        // 24-hour format (14:30, 09:00)
        if (preg_match('/\b(\d{1,2}):(\d{2})\b/', $message, $matches)) {
            $hour = (int)$matches[1];
            $minute = (int)$matches[2];
            if ($hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59) {
                return sprintf('%02d:%02d', $hour, $minute);
            }
        }
        
        // 12-hour format (2pm, 9:30am, 10 am)
        if (preg_match('/\b(\d{1,2})(?::(\d{2}))?\s*(am|pm)\b/i', $message, $matches)) {
            $hour = (int)$matches[1];
            $minute = isset($matches[2]) ? (int)$matches[2] : 0;
            $ampm = strtolower($matches[3]);
            
            if ($hour >= 1 && $hour <= 12 && $minute >= 0 && $minute <= 59) {
                if ($ampm === 'pm' && $hour !== 12) {
                    $hour += 12;
                } elseif ($ampm === 'am' && $hour === 12) {
                    $hour = 0;
                }
                return sprintf('%02d:%02d', $hour, $minute);
            }
        }
        
        // Time keywords
        foreach ($this->timeKeywords as $keyword => $time) {
            if (strpos($message, $keyword) !== false) {
                return $time;
            }
        }
        
        return null;
    }

    private function combineDateAndTime(?Carbon $date, ?string $time): ?Carbon
    {
        if (!$date) {
            return null;
        }
        
        if ($time) {
            [$hour, $minute] = explode(':', $time);
            $date->setTime((int)$hour, (int)$minute);
        }
        
        return $date;
    }

    private function extractPriority(string $message): string
    {
        foreach ($this->priorityKeywords as $keyword => $priority) {
            if (strpos($message, $keyword) !== false) {
                return $priority;
            }
        }
        
        return 'medium'; // Default priority
    }

    private function generateDescription(string $message): string
    {
        // Clean up the original message for description
        $description = preg_replace('/^(i have|i need to|i should|i must|remind me to|schedule|task:)/i', '', $message);
        return trim($description) ?: 'Task created via AI assistant';
    }

    private function extractTags(string $message): array
    {
        $tags = [];
        
        // Look for hashtags
        if (preg_match_all('/#(\w+)/', $message, $matches)) {
            $tags = array_merge($tags, $matches[1]);
        }
        
        // Auto-tag based on content
        if (preg_match('/\b(meeting|call|appointment)\b/', $message)) {
            $tags[] = 'meeting';
        }
        
        if (preg_match('/\b(buy|purchase|shopping)\b/', $message)) {
            $tags[] = 'shopping';
        }
        
        if (preg_match('/\b(work|project|office)\b/', $message)) {
            $tags[] = 'work';
        }
        
        if (preg_match('/\b(personal|home|family)\b/', $message)) {
            $tags[] = 'personal';
        }
        
        return array_unique($tags);
    }

    private function calculateConfidence(string $message, string $title, ?Carbon $date): float
    {
        $confidence = 0;
        
        // Has task keywords
        foreach ($this->taskKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                $confidence += 20;
                break;
            }
        }
        
        // Has a proper title
        if ($title && $title !== 'New Task') {
            $confidence += 30;
        }
        
        // Has date information
        if ($date) {
            $confidence += 30;
        }
        
        // Has time information
        if ($this->extractTime($message)) {
            $confidence += 20;
        }
        
        return min($confidence / 100, 1.0);
    }

    public function isTaskMessage(string $message): bool
    {
        $message = strtolower($message);
        
        // Check for task-related keywords
        foreach ($this->taskKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                return true;
            }
        }
        
        // Check for task-related phrases
        $taskPhrases = [
            'i have', 'i need to', 'i should', 'i must', 'remind me',
            'schedule', 'appointment', 'meeting', 'task'
        ];
        
        foreach ($taskPhrases as $phrase) {
            if (strpos($message, $phrase) !== false) {
                return true;
            }
        }
        
        return false;
    }
}