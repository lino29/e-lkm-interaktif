<?php

use App\Services\Learning\MediaHelper;

it('extracts youtube embed url from standard watch url', function () {
    $url = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
    $embedUrl = MediaHelper::getYoutubeEmbedUrl($url);

    expect($embedUrl)->toBe('https://www.youtube.com/embed/dQw4w9WgXcQ');
});

it('extracts youtube embed url from youtu.be short url', function () {
    $url = 'https://youtu.be/dQw4w9WgXcQ';
    $embedUrl = MediaHelper::getYoutubeEmbedUrl($url);

    expect($embedUrl)->toBe('https://www.youtube.com/embed/dQw4w9WgXcQ');
});

it('strips extra query parameters from youtube url', function () {
    $url = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ&feature=youtu.be&t=10s';
    $embedUrl = MediaHelper::getYoutubeEmbedUrl($url);

    expect($embedUrl)->toBe('https://www.youtube.com/embed/dQw4w9WgXcQ');
});

it('sanitizes embed code by removing script tags', function () {
    $code = '<iframe src="https://phet.colorado.edu/sims/html/energy-forms-and-changes/latest/energy-forms-and-changes_in.html" width="800" height="600" scrolling="no" allowfullscreen></iframe><script>alert("xss")</script>';
    $sanitized = MediaHelper::sanitizeEmbedCode($code);

    expect($sanitized)->not->toContain('<script>');
    expect($sanitized)->toContain('<iframe');
});
