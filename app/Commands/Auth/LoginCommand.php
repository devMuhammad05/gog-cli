<?php

namespace App\Commands\Auth;

use App\Services\AuthService;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class LoginCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:login';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Authenticate with Google Gmail';


    /**
     * Execute the console command.
     */
    public function handle(AuthService $authService)
    {

        if ($authService->isAuthenticated()) {
            $this->info('✅Already authenticated!');
            // $this->info('Access Token: ' . json_encode($authService->getToken(), JSON_PRETTY_PRINT));

            if (!$this->confirm('Do you want to re-authenticate?', false)) {
                return self::SUCCESS;
            }

            $authService->logout();
        }

        $this->info("Not authenticated");

        $this->info('🔐 Starting Google Authentication...');
        $this->newLine();

        $authUrl = $authService->getAuthUrl();

        $this->line('Please visit this URL to authorize the application:');
        $this->newLine();
        $this->line($authUrl);
        $this->newLine();

        // Try to open in browser automatically
        if ($this->confirm('Open this URL in your browser?', true)) {
            $this->openInBrowser($authUrl);
        }

        $this->info('Waiting for authentication from your browser...');

        $authCode = $this->startLoopbackServer();

        if (!$authCode) {
            $this->error('Failed to capture the authorization code automatically.');
            $authCode = $this->ask('Please enter the code manually if you have it');
        }

        if (!$authCode) {
            $this->error('Authorization code is required');
            return 1;
        }

        try {
            if ($authService->authenticate($authCode)) {
                $this->info('✅ Successfully authenticated!');
            }
        } catch (\Exception $e) {
            $this->error('❌ Authentication failed: ' . $e->getMessage());
            return 1;
        }
    }

    protected function startLoopbackServer(): ?string
    {
        $port = parse_url(config('google.redirect_uri'), PHP_URL_PORT) ?: 8080;
        $server = stream_socket_server("tcp://127.0.0.1:$port", $errno, $errstr);

        if (!$server) {
            $this->error("Could not start local server: $errstr ($errno)");
            return null;
        }

        $this->info("Listening for redirect on http://127.0.0.1:$port ...");

        // Increase timeout to 300 seconds (5 minutes)
        $conn = stream_socket_accept($server, 300);
        if (!$conn) {
            $this->error("Timed out waiting for authentication.");
            return null;
        }

        $request = fgets($conn);
        preg_match('/code=([^& ]+)/', $request, $matches);
        $code = $matches[1] ?? null;

        $response = "HTTP/1.1 200 OK\r\nContent-Type: text/html\r\n\r\n";
        $response .= "<html><body style='font-family:sans-serif;text-align:center;padding:50px;'>";
        $response .= "<h1>✅ Authentication Successful!</h1><p>You can close this tab and return to your terminal.</p></body></html>";

        fwrite($conn, $response);
        fclose($conn);
        fclose($server);

        return $code ? urldecode($code) : null;
    }


    protected function openInBrowser(string $url): void
    {
        $os = PHP_OS_FAMILY;

        try {
            switch ($os) {
                case 'Darwin': // macOS
                    exec("open " . escapeshellarg($url));
                    break;
                case 'Windows':
                    exec('start "" ' . escapeshellarg($url));
                    break;
                case 'Linux':
                    exec("xdg-open " . escapeshellarg($url));
                    break;
            }
        } catch (\Exception $e) {
            // Silently fail if can't open browser
        }
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
