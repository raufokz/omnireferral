<footer class="site-footer">
    <div class="footer-trust-strip">
        <div class="container footer-trust-grid">
            <div>
                <strong>24-hour response</strong>
                <p>Fast follow-up for every verified inquiry.</p>
            </div>
            <div>
                <strong>3,200+ verified leads</strong>
                <p>Proven pipeline volume for active teams.</p>
            </div>
            <div>
                <strong>Licensed network</strong>
                <p>Local agents, brokers, and admin support ready to respond.</p>
            </div>
            <div>
                <strong>Live support hours</strong>
                <p>Mon–Fri, 8am–7pm local business time.</p>
            </div>
        </div>
    </div>
    <div class="footer-cta-band">
        <div class="container footer-cta-band__inner">
            <div>
                <p><strong>Need help choosing a package?</strong></p>
                <p>Talk to our team for a guided recommendation that fits your market and budget.</p>
            </div>
            <div class="footer-cta-actions">
                <a href="{{ route('pricing') }}" class="button button--orange">Talk to our team</a>
                <a href="{{ route('contact') }}" class="button button--ghost">Contact Sales</a>
            </div>
        </div>
    </div>
    <div class="container footer-grid">
        <div>
            <h3>Platform</h3>
            <a href="{{ route('about') }}">About Us</a>
            <a href="{{ route('pricing') }}">Pricing</a>
            <a href="{{ route('agents.index') }}">Agent Directory</a>
            <a href="{{ route('blog.index') }}">Blog</a>
        </div>
        <div>
            <h3>For Agents</h3>
            <a href="{{ route('agents.index') }}">Find Agents</a>
            <a href="{{ route('pricing') }}">Packages</a>
            <a href="{{ route('contact') }}">Agent Support</a>
            <a href="{{ route('surveys') }}">Campaign Tools</a>
        </div>
        <div>
            <h3>For Buyers &amp; Sellers</h3>
            <a href="{{ route('listings') }}">Listings</a>
            <a href="{{ route('contact') }}">Request Match</a>
            <a href="{{ route('reviews') }}">Testimonials</a>
            <a href="{{ route('faq') }}">FAQ</a>
        </div>
        <div>
            <h3>Legal</h3>
            <a href="{{ route('privacy') }}">Privacy Policy</a>
            <a href="{{ route('terms') }}">Terms of Service</a>
            <a href="{{ route('payment.policy') }}">Payment &amp; Cancellation</a>
            <a href="{{ route('scam.prevention') }}">Scam Prevention</a>
            <a href="{{ route('communication.policy') }}">Communication Policy</a>
            <a href="{{ route('privacy') }}#cookies">Cookie Policy</a>
            <a href="{{ route('privacy') }}#accessibility">Accessibility</a>
            <a href="{{ route('sitemap') }}">Sitemap</a>
        </div>
    </div>
    <div class="container footer-bottom">
        <span>&copy; {{ now()->year }} OmniReferral. All rights reserved.</span>
        <div class="footer-legal-links">
            <a href="{{ route('privacy') }}">Privacy</a>
            <a href="{{ route('terms') }}">Terms</a>
            <a href="{{ route('payment.policy') }}">Payments</a>
            <a href="{{ route('scam.prevention') }}">Scam Prevention</a>
            <a href="{{ route('communication.policy') }}">Communication</a>
            <a href="{{ route('privacy') }}#cookies">Cookies</a>
            <a href="{{ route('privacy') }}#accessibility">Accessibility</a>
            <a href="{{ route('sitemap') }}">Sitemap</a>
        </div>
    </div>
</footer>
