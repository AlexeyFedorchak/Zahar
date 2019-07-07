<?php

namespace App\Logo;

use App\User;

/**
 * custom controller for getting different gmail custom images
 * this controller contain all the custom builders of some custom settings for some images.
 * it is necessary to avoid writing the same code again | storage of LogoGmail custom settings builders
 *
 * Class LogoTools
 * @package App\Logo
 */
class GmailTools
{
    /**
     * articles in the name of companies | has to be skipped
     *
     * @var array
     */
    protected static $articles = [
        'The',
        'the',
        'A',
        'a',
        'An',
        'an',
    ];

    /**
    * the user for which logo will be created
    * Laravel Model App\User
    */
    protected $user;

    /**
     * handle all controller creating
     *
     * LogoTools constructor.
     */
    private function __construct(){}

    /**
     * get controller instance to avoid new Class()..
     *
     * @return LogoTools
     */
    public static function getInstance()
    {
        return new self;
    }

    /**
     * gen logo for user
     * [75, 175] - the colors range that is used by Google Gmail. I'm sory, I'm stealer... :((
     *
     * @param User $user
     */
    public static function createGmailLogo(User $user)
    {
        $temporaryLogo = new LogoGmail($user, [75, 175]);
        $temporaryLogo->setHeader()
            ->setBackground(300,300)
            ->setUserDefaultBackground()
            ->setTextColor(255,255,255)
            ->presetText(self::getLetter($user))
            ->useText(110)
            ->createPNG()
            ->save()
            ->destroy();
    }

    /**
     * get two first letters, skipping articles
     *
     * @param User $user
     * @return string
     */
    private static function getLetter(User $user)
    {
        $words =
            collect(
                array_filter(
                    explode(' ', $user->name),
                    function ($word) {
                        return array_search($word, self::$articles) === FALSE && strlen($word) > 2;
                    })
            );

        return $words->first()[0] . $words->last()[0];
    }

    //put here more functiions to build unique logos...
}