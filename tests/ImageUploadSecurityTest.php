<?php

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Ladmin\Form\Field\Image;

class ImageUploadSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('admin');
    }

    public function testImageFieldRejectsExecutableExtensionsEvenForValidImageContent()
    {
        foreach (['avatar.php', 'avatar.php8', 'avatar.pht', 'avatar.phtml', 'avatar.phar', '.htaccess', '.user.ini'] as $name) {
            $file = UploadedFile::fake()->image($name);
            $field = $this->imageFieldFor($file);
            $validator = $field->getValidator(['avatar' => $file]);

            $this->assertNotFalse($validator, "No validation rules were retained for {$name}.");
            $this->assertTrue($validator->fails(), "The image field accepted {$name}.");
        }
    }

    public function testImageFieldRefusesDangerousExplicitStorageNames()
    {
        foreach (['avatar.php', 'avatar.php8', 'avatar.pht', 'avatar.phtml', 'avatar.phar', '.htaccess', '.user.ini', 'avatar.php.jpg'] as $name) {
            $file = UploadedFile::fake()->image('avatar.png');
            $field = $this->imageFieldFor($file)->name($name);

            try {
                $field->prepare($file);
                $this->fail("The image field accepted the storage name {$name}.");
            } catch (ValidationException) {
                Storage::disk('admin')->assertMissing("images/{$name}");
            }
        }
    }

    public function testImageFieldKeepsNormalPngUploadsWorking()
    {
        $file = UploadedFile::fake()->image('avatar.png');
        $field = $this->imageFieldFor($file);
        $validator = $field->getValidator(['avatar' => $file]);

        $this->assertNotFalse($validator);
        $this->assertFalse($validator->fails());
        $this->assertSame('images/avatar.png', $field->prepare($file));
        Storage::disk('admin')->assertExists('images/avatar.png');
    }

    private function imageFieldFor(UploadedFile $file): Image
    {
        $request = Request::create('/admin/auth/setting', 'PUT', [], [], ['avatar' => $file]);
        $this->app->instance('request', $request);

        return new Image('avatar', ['Avatar']);
    }
}
