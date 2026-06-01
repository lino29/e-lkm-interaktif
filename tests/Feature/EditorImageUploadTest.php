<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('teacher can upload editor image to public editor images storage', function () {
    $this->seed(DatabaseSeeder::class);
    Storage::fake('public');

    $teacher = User::where('email', 'guru@elkm.test')->firstOrFail();
    $image = UploadedFile::fake()->image('materi.png', 640, 360);

    $response = $this
        ->actingAs($teacher)
        ->withHeader('Accept', 'application/json')
        ->post(route('guru.uploads.editor-image'), [
            'upload' => $image,
        ]);

    $response
        ->assertSuccessful()
        ->assertJsonPath('uploaded', 1)
        ->assertJsonPath('default', $response->json('url'))
        ->assertJsonStructure(['uploaded', 'url', 'default']);

    $path = str($response->json('url'))->after('/storage/')->toString();

    expect($path)->toStartWith('editor-images/');
    Storage::disk('public')->assertExists($path);
});

test('editor image upload rejects non image files', function () {
    $this->seed(DatabaseSeeder::class);
    Storage::fake('public');

    $teacher = User::where('email', 'guru@elkm.test')->firstOrFail();

    $response = $this
        ->actingAs($teacher)
        ->postJson(route('guru.uploads.editor-image'), [
            'upload' => UploadedFile::fake()->create('materi.pdf', 20, 'application/pdf'),
        ]);

    expect($response->status())->toBe(422);
    expect($response->json('errors.upload'))->not->toBeEmpty();

    Storage::disk('public')->assertMissing('editor-images/materi.pdf');
});
