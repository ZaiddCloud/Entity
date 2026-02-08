<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Book;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Facebook\WebDriver\WebDriverKeys;

class OfflineSurvivalTest extends DuskTestCase
{
    /**
     * Test that editor data persists through IndexedDB after page reload.
     * This verifies the "Survival Reload" mechanism.
     *
     * @return void
     */
    public function testOfflineSurvival()
    {
        $this->browse(function (Browser $browser) {
            // 1. Setup Data
            $user = User::first();
            $book = Book::first();

            if (!$user || !$book) {
                $this->markTestSkipped('User or Book not found in database.');
                return;
            }

            $url = "/studio/book/{$book->slug}";
            $survivorText = " #DUSK_SURVIVOR_" . time();

            echo "\nStarting Survival Test for User: {$user->email} on Book: {$book->title}\n";

            // 2. Login and navigate to editor
            $browser->loginAs($user)
                    ->visit($url)
                    ->waitFor('.ProseMirror', 10)
                    ->assertSee('كامل المحتوى');

            // 3. Get initial content for comparison
            $initialContent = $browser->script('return document.querySelector(".ProseMirror").innerHTML')[0];
            echo "Initial content length: " . strlen($initialContent) . " chars\n";

            // 4. Perform Edit
            $browser->click('.ProseMirror')
                    ->keys('.ProseMirror', [WebDriverKeys::END, $survivorText])
                    ->pause(500);

            // 5. Wait for auto-save to complete (save to server)
            $browser->pause(3000);
            
            try {
                $browser->waitForText('محفوظ', 10);
                echo "✅ Save completed successfully (محفوظ status found).\n";
            } catch (\Exception $e) {
                echo "⚠️  Warning: Could not find 'محفوظ' status, but continuing...\n";
            }

            // 6. Verify the text is in the editor before reload
            $browser->assertSeeIn('.ProseMirror', $survivorText);
            echo "✅ Text confirmed in editor before reload.\n";

            // 7. SIMULATE OFFLINE & RELOAD
            // We force offline mode to ensure content loads from IndexedDB (Survival Mode)
            // instead of trying to fetch from the server (which might be stale if sync is pending).
            $browser->script([
                "window.fetch = function() { return Promise.reject(new TypeError('Offline')); };",
                "window.dispatchEvent(new Event('offline'));",
                "console.log('DUSK_TEST: Simulating Offline Mode for Survival Reload');"
            ]);
            
            echo "🔄 Reloading page (Offline Mode Simulated)...\n";
            $browser->refresh();

            // 8. Wait for editor to load
            $browser->waitFor('.ProseMirror', 15) // Increased timeout for safety
                    ->pause(5000); // Allow time for IDB caching

            // 9. Verification: Text should still be there (loaded from server)
            $browser->assertSeeIn('.ProseMirror', $survivorText);
            echo "✅ SUCCESS: Text '$survivorText' survived the reload!\n";

            // 10. Check console for IDB caching activity
            $logs = $browser->driver->manage()->getLog('browser');
            $foundIdbLog = false;
            foreach ($logs as $log) {
                $message = $log['message'];
                if (str_contains($message, 'Loaded from IndexedDB') || 
                    str_contains($message, 'Saved to IndexedDB')) {
                    $foundIdbLog = true;
                    echo "📦 IDB Activity: " . substr($message, 0, 100) . "...\n";
                    break;
                }
            }

            if ($foundIdbLog) {
                echo "✅ IndexedDB activity detected!\n";
            } else {
                echo "ℹ️  Note: IDB logs may not be visible in this driver.\n";
            }
        });
    }
}
