<?php

namespace App\Console\Commands;

use App\Mail\ContactMessageMail;
use App\Mail\EstimateSentMail;
use App\Mail\EstimateShareOtpMail;
use App\Mail\InvoiceIssuedMail;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailTemplates extends Command
{
    protected $signature = 'email:test {email : Email address to send test emails to}';

    protected $description = 'Send test emails for all templates to verify styling and layout';

    public function handle()
    {
        $toEmail = $this->argument('email');
        
        $this->info("📧 Sending test emails to: {$toEmail}");
        $this->newLine();

        // 1. Contact Message Email
        $this->sendContactMessage($toEmail);

        // 2. Customer OTP Email
        $this->sendCustomerOtp($toEmail);

        // 3. Order Submitted Email
        $this->sendOrderSubmitted($toEmail);

        // 4. Invoice Issued Email
        $this->sendInvoiceIssued($toEmail);

        // 5. Estimate Sent Email
        $this->sendEstimateSent($toEmail);

        // 6. Estimate Share OTP Email
        $this->sendEstimateShareOtp($toEmail);

        // 7. Verify Email Address
        $this->sendVerifyEmail($toEmail);

        // 8. Contact Form Alternative
        $this->sendContactFormAlt($toEmail);

        $this->newLine();
        $this->info('✅ All test emails sent successfully!');
        $this->info("📬 Check {$toEmail} inbox");
        
        return 0;
    }

    private function sendContactMessage($toEmail)
    {
        $this->line('Sending: Contact Message Email...');
        
        Mail::to($toEmail)->send(new ContactMessageMail([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+94771234567',
            'subject' => 'Inquiry about Printing Services',
            'message' => "Hello,\n\nI am interested in your bulk printing services. Could you please provide me with a quote for 1000 business cards?\n\nThank you!",
        ]));
        
        $this->info('✓ Contact Message sent');
    }

    private function sendCustomerOtp($toEmail)
    {
        $this->line('Sending: Customer OTP Email...');
        
        Mail::send('emails.customer-otp', [
            'otp' => '123456',
            'expiresMinutes' => 7,
        ], function ($message) use ($toEmail) {
            $message->to($toEmail)
                    ->subject('Verification Code – Printair');
        });
        
        $this->info('✓ Customer OTP sent');
    }

    private function sendOrderSubmitted($toEmail)
    {
        $this->line('Sending: Order Submitted Email...');
        
        // Create a mock order
        $order = new Order([
            'id' => 12345,
            'order_no' => 'ORD-2025-12345',
        ]);
        $order->id = 12345;
        
        Mail::send('emails.order-submitted', [
            'order' => $order,
            'secureUrl' => 'https://printair.lk/orders/secure/abc123xyz',
        ], function ($message) use ($toEmail) {
            $message->to($toEmail)
                    ->subject('Order Submitted – Printair');
        });
        
        $this->info('✓ Order Submitted sent');
    }

    private function sendInvoiceIssued($toEmail)
    {
        $this->line('Sending: Invoice Issued Email...');
        
        // Try to find a real invoice, or create mock data
        $invoice = Invoice::first() ?? new Invoice([
            'id' => 100,
            'invoice_no' => 'INV-2025-100',
        ]);
        
        if (!$invoice->id) {
            $invoice->id = 100;
        }
        
        Mail::to($toEmail)->send(new InvoiceIssuedMail(
            invoice: $invoice,
            invoiceUrl: 'https://printair.lk/invoices/secure/abc123xyz',
            pdfUrl: 'https://printair.lk/invoices/secure/abc123xyz/download',
            pdfBinary: 'Mock PDF Binary Content'
        ));
        
        $this->info('✓ Invoice Issued sent');
    }

    private function sendEstimateSent($toEmail)
    {
        $this->line('Sending: Estimate Sent Email...');
        
        // Try to find a real estimate, or create mock data
        $estimate = Estimate::first() ?? new Estimate([
            'id' => 200,
            'estimate_no' => 'EST-2025-200',
            'grand_total' => 25000.00,
            'currency' => 'LKR',
            'valid_until' => now()->addDays(30),
            'customer_snapshot' => [
                'full_name' => 'Jane Smith',
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
            ],
        ]);
        
        if (!$estimate->id) {
            $estimate->id = 200;
        }
        
        Mail::to($toEmail)->send(new EstimateSentMail(
            estimate: $estimate,
            publicUrl: 'https://printair.lk/estimates/public/xyz789abc',
            pdfBytes: 'Mock PDF Binary Content',
            pdfFilename: 'Estimate-EST-2025-200.pdf',
            meta: []
        ));
        
        $this->info('✓ Estimate Sent sent');
    }

    private function sendEstimateShareOtp($toEmail)
    {
        $this->line('Sending: Estimate Share OTP Email...');
        
        $estimate = Estimate::first() ?? new Estimate([
            'id' => 200,
            'estimate_no' => 'EST-2025-200',
            'customer_snapshot' => [
                'full_name' => 'Jane Smith',
                'name' => 'Jane Smith',
            ],
        ]);
        
        if (!$estimate->id) {
            $estimate->id = 200;
        }
        
        Mail::to($toEmail)->send(new EstimateShareOtpMail(
            estimate: $estimate,
            code: '789012',
            expiresMinutes: 15
        ));
        
        $this->info('✓ Estimate Share OTP sent');
    }

    private function sendVerifyEmail($toEmail)
    {
        $this->line('Sending: Verify Email Address...');
        
        // Create or find a user
        $user = User::first() ?? new User([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $toEmail,
        ]);
        
        if (!$user->id) {
            $user->id = 1;
        }
        
        try {
            $notification = new VerifyEmailNotification();
            Mail::send('emails.auth.verify-email', [
                'user' => $user,
                'url' => 'https://printair.lk/verify-email?token=abc123xyz456',
            ], function ($message) use ($toEmail) {
                $message->to($toEmail)
                        ->subject('Verify Your Email – Printair');
            });
            
            $this->info('✓ Verify Email sent');
        } catch (\Exception $e) {
            $this->warn('⚠ Verify Email skipped: ' . $e->getMessage());
        }
    }

    private function sendContactFormAlt($toEmail)
    {
        $this->line('Sending: Contact Form (Alternative)...');
        
        Mail::send('emails.contact.message', [
            'p' => [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.johnson@example.com',
                'phone' => '+94777654321',
                'subject' => 'Bulk Order Inquiry',
                'message' => "Hi Team,\n\nWe need to print 500 brochures for our upcoming event. Can you provide a quote and timeline?\n\nBest regards,\nSarah",
                'ip' => '192.168.1.100',
                'ua' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0',
            ],
        ], function ($message) use ($toEmail) {
            $message->to($toEmail)
                    ->subject('Contact Form Submission – Printair');
        });
        
        $this->info('✓ Contact Form (Alt) sent');
    }
}
