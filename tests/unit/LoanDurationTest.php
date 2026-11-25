<?php

use CodeIgniter\I18n\Time;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class LoanDurationTest extends CIUnitTestCase
{
    use \CodeIgniter\Test\DatabaseTestTrait;

    // null = jalankan seluruh namespace migration (App, Shield, Settings)
    protected $namespace = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Sama dengan nilai di server production; Shield menolak
        // csrfProtection 'cookie' saat view sidebar memanggil auth().
        config('Security')->csrfProtection = 'session';

        $this->stubAuth();
    }

    /**
     * Layout admin memanggil auth()->user(), jadi user palsu perlu disuntikkan
     * agar form peminjaman bisa dirender tanpa sesi login.
     */
    private function stubAuth(): void
    {
        $user = $this->createMock(\CodeIgniter\Shield\Entities\User::class);
        $user->method('inGroup')->willReturn(true);
        $user->method('getGroups')->willReturn(['superadmin']);
        $user->method('__get')->willReturnCallback(
            static fn (string $key) => $key === 'email' ? 'superadmin@admin.com' : 'superadmin'
        );

        $auth = $this->createMock(\CodeIgniter\Shield\Auth::class);
        $auth->method('user')->willReturn($user);
        // helper auth() memanggil setAuthenticator() dan memakai hasilnya
        $auth->method('setAuthenticator')->willReturnSelf();

        \Config\Services::injectMock('auth', $auth);
    }

    private function rules(): array
    {
        return ['duration-buku' => 'required|numeric|integer|greater_than[0]|less_than_equal_to[' . MAX_LOAN_DURATION . ']'];
    }

    private function isValid(string $duration): bool
    {
        $validation = \Config\Services::validation();
        $validation->setRules($this->rules());

        return $validation->run(['duration-buku' => $duration]);
    }

    /**
     * Durasi tidak lagi dibatasi pada 7/14/30 hari.
     */
    public function testAcceptsArbitraryDuration()
    {
        foreach (['1', '3', '10', '21', '45', '90', '365'] as $duration) {
            $this->assertTrue($this->isValid($duration), "Durasi {$duration} hari harus valid");
        }
    }

    public function testRejectsInvalidDuration()
    {
        $this->assertFalse($this->isValid('0'));
        $this->assertFalse($this->isValid('-5'));
        $this->assertFalse($this->isValid('7.5'));
        $this->assertFalse($this->isValid('abc'));
        $this->assertFalse($this->isValid(''));
        $this->assertFalse($this->isValid((string) (MAX_LOAN_DURATION + 1)));
    }

    public function testDueDateFollowsCustomDuration()
    {
        foreach ([1, 21, 45] as $duration) {
            $loanDate = Time::parse('2026-01-15 09:00:00');
            $dueDate = $loanDate->addDays($duration);

            $this->assertSame($duration, $loanDate->diff($dueDate)->days);
            $this->assertSame(
                $loanDate->modify('+' . $duration . ' days')->toDateString(),
                $dueDate->toDateString()
            );
        }
    }

    /**
     * Form peminjaman harus offering input durasi bebas, bukan radio 7/14/30.
     */
    public function testCreateFormUsesFreeDurationInput()
    {
        $output = view('loans/create', [
            'books' => [
                [
                    'slug'        => 'buku-uji',
                    'title'       => 'Buku Uji',
                    'year'        => 2024,
                    'author'      => 'Penulis',
                    'publisher'   => 'Penerbit',
                    'book_cover'  => 'default.jpg',
                    'stock'       => 3,
                ],
            ],
            'member' => [
                'uid'        => 'abc123',
                'first_name' => 'Ustadz',
                'last_name'  => 'Khoerudin',
                'email'      => 'ustadz@example.com',
                'phone'      => '08123456789',
                'address'    => 'Cileungsi',
            ],
            'validation' => \Config\Services::validation(),
            'oldInput'   => null,
        ]);

        $this->assertStringContainsString('type="number"', $output);
        $this->assertStringContainsString('name="duration-buku-uji"', $output);
        $this->assertStringContainsString('value="' . DEFAULT_LOAN_DURATION . '"', $output);
        $this->assertStringContainsString('max="' . MAX_LOAN_DURATION . '"', $output);
        $this->assertStringNotContainsString('type="radio"', $output);
    }
}
