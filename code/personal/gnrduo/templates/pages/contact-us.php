<div tabindex="0">
    <p>Ready to discuss your event or venue's unique musical needs?&nbsp;  
        We'd love to connect with you and discuss how we can tailor our services 
        to provide the perfect musical experience for your occasion!
    </p>
    <p>For inquiries, bookings, or to learn more about our offerings, please don't hesitate to get in touch with us.</p>
    <p>We look forward to speaking with and performing for you - thank you for considering <?= $siteName ?>!</p>
</div>
<div tabindex="0">
    <div class="top py-10 font-size-24 font-weight-bold">Call Us:</div>
    <div>
        <div>
            <span class="me-10">Rod:</span>
            <a 
                href="tel:<?= str_replace(" ", "", $contactInfo['rodPhone']) ?>" 
                title="Call Rod"
            >
                <?= $contactInfo['rodPhone'] ?>
            </a>
        </div>
        <div>
            <span class="me-10">George:</span>
            <a 
                href="tel:<?= str_replace(" ", "", $contactInfo['georgePhone']) ?>" 
                title="Call George"
            >
                <?= $contactInfo['georgePhone'] ?>
            </a>
        </div>
    </div>
</div>
<div class="social_links" tabindex="0">
    <div class="top py-20 font-size-24 font-weight-bold">Follow us on Social Media:</div>
    <div class="bottom">
        <?= $socialLinks ?>
    </div>
</div>