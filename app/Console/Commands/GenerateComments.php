<?php

namespace App\Console\Commands;

use App\Models\Comment;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use App\Enums\ContentStatus;

class GenerateComments extends Command
{
    /**
     * @var string
     */
    protected $signature = 'comments:generate {count=80 : Number of comments to generate}';

    /**
     * @var string
     */
    protected $description = 'Generate positive Arabic comments using GPT API and save them to the database';


    /**
     * The maximum number of comments to generate in a single API request.
     * Using a smaller batch size (25) for the standard gpt-3.5-turbo model
     * since it has a smaller context window than the 16k version.
     *
     * @var int
     */
    protected int $batchSize = 40;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $totalCount = $this->argument('count');
        $this->info("Generating {$totalCount} positive Arabic comments...");

        $batches = ceil($totalCount / $this->batchSize);
        $remainingComments = $totalCount;
        $totalGenerated = 0;

        $this->info("Processing in {$batches} batch(es)");
        $bar = $this->output->createProgressBar($totalCount);
        $bar->start();

        for ($i = 0; $i < $batches; $i++) {
            $batchCount = min($this->batchSize, $remainingComments);
            $this->info("\nGenerating batch " . ($i + 1) . " of {$batches} ({$batchCount} comments)");

            try {
                $comments = $this->generateArabicCommentsBatch($batchCount);
                $savedCount = $this->saveCommentsToDatabase($comments);

                $totalGenerated += $savedCount;
                $bar->advance($savedCount);
                $remainingComments -= $batchCount;

                $this->info("Saved {$savedCount} comments from batch " . ($i + 1));

                // Add a small delay between batches to avoid API rate limits
                if ($i < $batches - 1) {
                    sleep(2);
                }
            } catch (\Exception $e) {
                $this->error("Error in batch " . ($i + 1) . ": " . $e->getMessage());
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info("Successfully generated and saved {$totalGenerated} comments to the database.");

        return Command::SUCCESS;
    }

    /**
     * Generate a batch of Arabic positive comments
     *
     * @param int $count
     * @return array
     * @throws ConnectionException
     */
    private function generateArabicCommentsBatch(int $count): array
    {
        $apiKey = config('services.openai.api_key');

        if (!$apiKey) {
            throw new \Exception('OpenAI API key not configured. Add OPENAI_API_KEY to your .env file.');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful assistant that generates realistic customer comments.'
                ],
                [
                    'role' => 'user',
                    'content' => "Generate {$count} different general positive comments in Arabic that can be used for product reviews in JSON format. The comments should not mention any specific product names but should highlight aspects like quality, durability, and overall experience. Each comment should be short, natural, and sound like real user feedback. Format the output as a JSON array of strings."
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 4000,
            'n' => 1,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to get response from OpenAI: ' . $response->body());
        }

        $result = $response->json();

        if (empty($result['choices'][0]['message']['content'])) {
            throw new \Exception('Empty response from GPT API');
        }

        $content = trim($result['choices'][0]['message']['content']);

        // Try to decode JSON content
        $commentsArray = json_decode($content, true);

        // If not valid JSON, try to extract JSON part
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Try to extract JSON array from the content using regex
            if (preg_match('/\[\s*".*"\s*\]/s', $content, $matches)) {
                $commentsArray = json_decode($matches[0], true);
            }
        }

        // If still not valid JSON, split by lines
        if (json_last_error() !== JSON_ERROR_NONE) {
            $lines = preg_split('/\r\n|\r|\n/', $content);
            $commentsArray = array_filter($lines, function($line) {
                return !empty(trim($line)) && !preg_match('/^\d+\./', $line);
            });
        }

        if (!is_array($commentsArray)) {
            throw new \Exception('Failed to parse comments from API response');
        }

        return $commentsArray;
    }

    /**
     * Save comments to the database
     *
     * @param array $comments
     * @return int Number of comments saved
     */
    private function saveCommentsToDatabase(array $comments): int
    {
        $savedCount = 0;

        foreach ($comments as $commentText) {
            // Skip non-string values or empty strings
            if (!is_string($commentText) || empty(trim($commentText))) {
                continue;
            }

            try {
                Comment::create([
                    'text' => $commentText,
                    'status' => ContentStatus::ACTIVE,
                    'used_count' => 0,
                ]);

                $savedCount++;
            } catch (\Exception $e) {
                $this->error("Error saving comment: " . $e->getMessage());
            }
        }

        return $savedCount;
    }
}
