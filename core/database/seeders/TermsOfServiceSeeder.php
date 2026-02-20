<?php

namespace Database\Seeders;

use App\Models\Frontend;
use Illuminate\Database\Seeder;

class TermsOfServiceSeeder extends Seeder
{
    public function run(): void
    {
        $title = 'Terms of Service';

        $html = <<<HTML
<p>Please read these Terms of Service ("Terms", "Terms of Service") carefully before using the Service operated by KNOWLEDGE WAVE INDIA (the "Company", "We", "Us", or "Our").</p>
<p>Your access to and use of the Service is conditioned on your acceptance of and compliance with these Terms. These Terms apply to all visitors, users and others who access or use the Service.</p>
<p>By accessing or using the Service you agree to be bound by these Terms. If you disagree with any part of the Terms then you may not access the Service.</p>

<h3>Interpretation and Definitions</h3>
<h4>Interpretation</h4>
<p>The words of which the initial letter is capitalized have meanings defined under the following conditions. The following definitions shall have the same meaning regardless of whether they appear in singular or in plural.</p>

<h4>Definitions</h4>
<p>For the purposes of these Terms of Service:</p>
<p><strong>Account</strong> means a unique account created for You to access our Service or parts of our Service.</p>
<p><strong>Company</strong> refers to KNOWLEDGE WAVE INDIA.</p>
<p><strong>Country</strong> refers to: Delhi, India.</p>
<p><strong>Device</strong> means any device that can access the Service such as a computer, a cellphone or a digital tablet.</p>
<p><strong>Service</strong> refers to the Website and related applications/features.</p>
<p><strong>Terms</strong> mean these Terms of Service.</p>
<p><strong>Website</strong> refers to KNOWLEDGE WAVE INDIA, accessible from WWW.KNOWLEDGEWAVEINDIA.COM</p>
<p><strong>You</strong> means the individual accessing or using the Service, or the company, or other legal entity on behalf of which such individual is accessing or using the Service, as applicable.</p>

<h3>Acknowledgement</h3>
<p>These are the Terms of Service governing the use of this Service and the agreement that operates between You and the Company. These Terms set out the rights and obligations of all users regarding the use of the Service.</p>
<p>By accessing or using the Service You agree to be bound by these Terms of Service. If You disagree with any part of these Terms of Service then You may not access the Service.</p>
<p>You represent that you are at least 18 years old. The Company does not permit those under 18 to use the Service.</p>

<h3>User Accounts</h3>
<p>When You create an account with Us, You must provide Us information that is accurate, complete, and current at all times. Failure to do so constitutes a breach of the Terms, which may result in immediate termination of Your account on Our Service.</p>
<p>You are responsible for safeguarding the password that You use to access the Service and for any activities or actions under Your password.</p>
<p>You must notify Us immediately upon becoming aware of any breach of security or unauthorized use of Your account.</p>

<h3>Prohibited Activities</h3>
<p>You agree not to use the Service:</p>
<ul>
  <li>For any unlawful purpose or in violation of any applicable laws.</li>
  <li>To impersonate any person or entity or otherwise misrepresent your affiliation with a person or entity.</li>
  <li>To attempt to gain unauthorized access to the Service, other accounts, computer systems, or networks connected to the Service.</li>
  <li>To interfere with or disrupt the integrity or performance of the Service.</li>
  <li>To upload or transmit viruses, malware, or any other malicious code.</li>
</ul>

<h3>Payments (If Applicable)</h3>
<p>If You wish to purchase any product or service made available through the Service ("Purchase"), You may be asked to supply certain information relevant to Your Purchase including, without limitation, billing details and payment information.</p>
<p>We reserve the right to refuse or cancel Your order at any time for reasons including but not limited to errors in the description or price, or suspected fraud or unauthorized or illegal transactions.</p>

<h3>Content</h3>
<p>Our Service may allow You to submit or share content ("Content"). You are responsible for the Content you submit, including its legality, reliability, and appropriateness.</p>
<p>By submitting Content, You grant Us a non-exclusive, worldwide, royalty-free license to use, reproduce, modify, and display such Content as necessary to operate and improve the Service.</p>

<h3>Intellectual Property</h3>
<p>The Service and its original content (excluding Content provided by You), features and functionality are and will remain the exclusive property of the Company and its licensors.</p>

<h3>Links to Other Websites</h3>
<p>Our Service may contain links to third-party websites or services that are not owned or controlled by the Company.</p>
<p>The Company has no control over and assumes no responsibility for the content, privacy policies, or practices of any third party websites or services. We strongly advise You to read the terms and privacy policies of any third-party websites or services that You visit.</p>

<h3>Termination</h3>
<p>We may terminate or suspend Your Account immediately, without prior notice or liability, for any reason whatsoever, including without limitation if You breach these Terms.</p>
<p>Upon termination, Your right to use the Service will cease immediately.</p>

<h3>Limitation of Liability</h3>
<p>To the maximum extent permitted by applicable law, in no event shall the Company be liable for any indirect, incidental, special, consequential or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses, resulting from (i) your access to or use of or inability to access or use the Service; (ii) any conduct or content of any third party on the Service; (iii) any content obtained from the Service; and (iv) unauthorized access, use or alteration of your transmissions or content.</p>

<h3>"AS IS" and "AS AVAILABLE" Disclaimer</h3>
<p>The Service is provided to You "AS IS" and "AS AVAILABLE" and with all faults and defects without warranty of any kind. We do not warrant that the Service will be uninterrupted, error-free, or free of harmful components.</p>

<h3>Governing Law</h3>
<p>The laws of Delhi, India, excluding its conflicts of law rules, shall govern these Terms and Your use of the Service.</p>

<h3>Changes to These Terms</h3>
<p>We reserve the right, at Our sole discretion, to modify or replace these Terms at any time. By continuing to access or use Our Service after those revisions become effective, You agree to be bound by the revised terms.</p>

<h3>Contact Us</h3>
<p>If you have any questions about these Terms, please contact us using the contact information available on the Website.</p>
HTML;

        $slug = 'terms-of-service';

        $existing = Frontend::where('data_keys', 'policy_pages.element')
            ->where('slug', $slug)
            ->orderBy('id', 'asc')
            ->get();

        if ($existing->count() > 0) {
            $keep = $existing->first();
            $dupes = $existing->slice(1);
            foreach ($dupes as $d) {
                $d->delete();
            }

            $keep->tempname = activeTemplateName();
            $keep->data_values = (object) [
                'title' => $title,
                // Keep both keys for compatibility (blade uses details, SPA uses description)
                'details' => $html,
                'description' => $html,
            ];
            $keep->save();
            return;
        }

        $frontend = new Frontend();
        $frontend->data_keys = 'policy_pages.element';
        $frontend->tempname = activeTemplateName();
        $frontend->slug = $slug;
        $frontend->data_values = (object) [
            'title' => $title,
            // Keep both keys for compatibility (blade uses details, SPA uses description)
            'details' => $html,
            'description' => $html,
        ];
        $frontend->save();
    }
}

