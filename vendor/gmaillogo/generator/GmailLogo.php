<?php
namespace App\Logo;

use App\User;
use Illuminate\Support\Facades\Storage;

/**
 * custom utilities for generating gmail logo
 *
 * Class LogoGmail
 * @package App
 */
class GmailLogo
{
    /**
     * user email, will be used for making new logo
     *
     * @var string
     */
    protected $user;

    /**
     * handler of an image
     *
     * @var resource
     */
    protected $imageResource;

    /**
     * keeps path to file
     *
     * @var string
     */
    protected $font;

    /**
     * image coloured text
     *
     * @var resource
     */
    protected $colouredText;

    /**
     * unique logoName (md5(uniqid(email)))
     *
     * @var string
     */
    protected $logoName;

    /**
     * get dump
     *
     * @var string
     */
    protected $imageContent;

    /**
     * color depth
     *
     * @var array [low color value, high color value]
     */
    protected $colorDepth;

    /**
     * show if text color inverting is recommended
     *
     * @var bool
     */
    protected $needsInvertTextColor = false;

    /**
     * letter using for creating logo
     *
     * @var string
     */
    protected $letter;

    /**
     * string using to determine if user has temporary icon from gmail
     *
     * @var string
     */
    protected $sign = 'CustomGmailTemporaryIcon';

    /**
     * width of the image
     *
     * @var int
     */
    protected $width;

    /**
     * height of the image
     *
     * @var
     */
    protected $height;

    /**
     * color stack '$red . | . $green . | . $blue'. Has to be put in DB ==> user(company)->logo_color.
     *
     * @var String
     */
    protected $colorStack = null;

    /**
    * Public path for fonts. 
    * The font should be put into public Laravel folder
    */
    protected $fontPath = 'fonts/roboto/RobotoRegular.ttf';

    /**
    * path to folder which contains images
    */
    protected $folderImagesPath = 'profile';

    /**
    * The attribute name of user model. 
    * The attribute should be fillable property of User laravel model.
    * Contains path to user logo in storage laravel folder. 
    * The storage should be linked to public folder
    * This param is not optional.
    */
    protected $userModelImagePathAttribute = 'logo';

    /**
    * The attribute contains stabel logo color. 
    * Sometimes it is necessary to update only text, but not color.
    * This attribute should be a fillable property fo Larevel model. If such doesn't exist, the images will 
    * not have stable color.
    * This attribute is optional
    */
    protected $userModelStableColorAttribute = 'logo_color';

    /**
     * init class | make initial settings
     *
     * LogoGmail constructor.
     * @param \App\User $user
     * @param array $colorDepth
     */
    public function __construct(User $user, array $colorDepth)
    {
        $this->user = $user;
        $this->logoName = 
        	$folderImagesPath 
        	. DIRECTORY_SEPARATOR 
        	. $this->sign 
        	. md5(uniqid($this->user->email));

        $this->colorDepth = $colorDepth;
        $this->font = public_path($this->fontPath);
        $this->setLetter();
    }

    /**
     * set header, have to use for drawing an image
     */
    public function setHeader()
    {
        header("Content-Type: image/png");
        return $this;
    }

    /**
     * get image from string
     *
     * @param $width integer
     * @param $height integer
     * @return $this
     */
    public function setBackground($width, $height)
    {
        $this->imageResource = imagecreate($width, $height);
        $this->width = $width;
        $this->height = $height;
        return $this;
    }

    /**
     * set colour for background
     *
     * @param $red integer (0...255)
     * @param $green integer (0...255)
     * @param $blue integer (0...255)
     * @return $this
     */
    public function setBackgroundColor($red, $green, $blue)
    {
        imagecolorallocate($this->imageResource, $red, $green, $blue);
        return $this;
    }

    /**
     * get background with random color
     *
     * @return $this
     */
    public function setRandomBackgroundColor()
    {
        $red = $this->getRandColor();
        $green = $this->getRandColor();
        $blue = $this->getRandColor();

        if (count(array_unique([$red, $green, $blue, $this->colorDepth[1]])) === 1) {
            $this->needsInvertTextColor = true;
        }

        $this->colorStack = implode('|', [$red, $green, $blue]);
        return $this->setBackgroundColor($red, $green, $blue);
    }

    /**
     * set default user background if such exists
     *
     * @return LogoGmail
     */
    public function setUserDefaultBackground()
    {
    	$logoStableColor = $this->userModelStableColorAttribute;

        if (exists($this->user->$logoStableColor) 
        	&& !is_null($this->user->$logoStableColor)) {
            $color = explode('|', $this->user->logo_color);
            $red = $color[0] ?? $this->getRandColor();
            $green = $color[1] ?? $this->getRandColor();
            $blue = $color[2] ?? $this->getRandColor();

            return $this->setBackgroundColor($red, $green, $blue);
        }

        return $this->setRandomBackgroundColor();
    }

    /**
     * returns coloured text
     *
     * @param $red integer (0...255)
     * @param $green integer (0...255)
     * @param $blue integer (0...255)
     * @return $this
     */
    public function setTextColor($red, $green, $blue)
    {
        if ($this->needsInvertTextColor) {
            $red = $this->colorDepth[0];
            $green = $this->colorDepth[0];
            $blue = $this->colorDepth[0];
        }

        $this->colouredText = imagecolorallocate($this->imageResource, $red, $green, $blue);
        return $this;
    }

    /**
     * use text for image
     *
     * @param $size int
     * @param $angle int
     * @param $x int
     * @param $y int
     * @return $this
     */
    public function useText(int $size, int $angle = 0, int $x = 0, int $y = 0)
    {
    	//centering text
        $bbox = imagettfbbox($size, 0, $this->font, $this->letter);
        $textHeight = abs($bbox[5]);
        $textWidth = $bbox[4] - $bbox[0];
        $y = $textHeight + 0.5 * ($this->height - $textHeight);
        $x = 0.5 * ($this->width - $textWidth) - $bbox[0];

        //put text into image
        imagettftext(
            $this->imageResource,
            $size,
            $angle,
            $x,
            $y,
            $this->colouredText,
            $this->font,
            $this->letter);

        return $this;
    }

    /**
     * set new text for image
     *
     * @param string $string
     * @return $this
     */
    public function presetText(string $string)
    {
        $this->letter = $string;
        return $this;
    }

    /**
     * create an image, header should be set before printing the image
     *
     * @return $this
     */
    public function createPNG()
    {
        $this->logoName .= '.png';
        $this->initBuffer();
        imagepng($this->imageResource);
        $this->getBufferAndClean();
        return $this;
    }

    /**
     * create an image, header should be set before printing the image
     *
     * @return $this
     */
    public function createJPEG()
    {
        $this->logoName .= '.jpeg';
        $this->initBuffer();
        imagejpeg($this->imageResource);
        $this->getBufferAndClean();
        return $this;
    }

    /**
     * helper for checking output PNG
     *
     * @return $this
     */
    public function showPNG()
    {
        imagepng($this->imageResource);
        return $this;
    }

    /**
     * helper for checking output JPEG
     *
     * @return $this
     */
    public function showJPEG()
    {
        imagejpeg($this->imageResource);
        return $this;
    }

    /**
     * get buffer and save to file
     *
     * @return $this
     */
    public function save()
    {
        $this->removePreviousTemporaryIcon();
        $disk = Storage::disk('public');

        $disk->put(
            $this->logoName, $this->imageContent
        );

        $logoAttributeName = $this->userModelImagePathAttribute;
        $logoStableColor = $this->userModelStableColorAttribute;

        $this->user->$logoAttributeName 
        	= str_replace('storage' . DIRECTORY_SEPARATOR, '', $disk->url($this->logoName));

        if (isset($this->user->$logoStableColor)) {
        	$this->user->$logoStableColor 
        		= !is_null($this->colorStack) ? $this->colorStack : $this->user->$logoStableColor;        	
        }

		$this->user->save();

        return $this;
    }
    /**
     * destroy image to prevent dump overloading
     *
     * @return $this
     */
    public function destroy()
    {
        imagedestroy($this->imageResource);
        return $this;
    }

    /**
     * start saving buffer
     */
    private function initBuffer()
    {
        ob_start();
    }

    /**
     * get content from buffer and clean buffer
     */
    private function getBufferAndClean()
    {
        $this->imageContent = ob_get_contents();
        ob_end_clean();
    }

    /**
     * rand color depth
     *
     * @return int
     */
    private function getRandColor()
    {
        return rand(0,1) === 0 ? $this->colorDepth[0] : $this->colorDepth[1];
    }

    /**
     * set letter for making new icon
     */
    private function setLetter()
    {
        if (isset($this->user->name) && !empty($this->user->name)) {
            $this->letter = $this->user->name[0];
        } else {
            $this->letter = $this->user->email[0];
        }
    }

    /**
     * remove temporary icon before creating new
     */
    private function removePreviousTemporaryIcon()
    {
        $fileName = explode($this->folderImagesPath, $this->user->photo_url)[1] ?? null;
            if (file_exists(
            	'app' 
            		. DIRECTORY_SEPARATOR 
            		. 'public' 
            		. DIRECTORY_SEPARATOR 
            		. $this->folderImagesPath 
            		. $fileName)
            	&& !empty($fileName) 
            	&& strpos($fileName, $this->sign) !== FALSE) 
                unlink(storage_path('app/public/profiles' . $fileName));
    }
}
