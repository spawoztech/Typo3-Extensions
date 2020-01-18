<?php
namespace SPT\SptSocialmedia\Controller;

/***
 *
 * This file is part of the "Social Media Widget" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2017 Arun Chandran <arun@spawoz.com>, Spawoz Technologies Pvt. Ltd
 *
 ***/

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * SocialmediaController
 */
class SocialmediaController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * socialmediaRepository
     *
     * @var \SPT\SptSocialmedia\Domain\Repository\SocialmediaRepository
     * @inject
     */
    protected $socialmediaRepository = null;

    /**
     * action list
     *
     * @return void
     */
    public function listAction()
    {
        $this->pageRenderer = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Page\\PageRenderer');
        $rootPageID = $GLOBALS['TSFE']->rootLine[0]['uid'];
        $socialmedias = ( $rootPageID ) ? $this->socialmediaRepository->findByPid($rootPageID) : $this->socialmediaRepository->findAll();
        foreach ($socialmedias as $key => $value) {
            $linkData = explode(' ', $value->getLink());
            $target = $linkData[1];
            if( $value->getType() != 'phone'  && $value->getType() != 'envelope-o' && $value->getType() != 'file-o' && $value->getType() != 'link' ) {
                if (!preg_match("~^(?:f|ht)tps?://~i", $linkData[0])) {                
                    $linkData[0] = "http://" . $linkData[0];                
                }
            }
            if ( $value->getType() == 'link' || $value->getType() == 'file-o' ) {
                $linkData[0] = $this->controllerContext->getUriBuilder()->reset()->setTargetPageUid($linkData[0])->buildFrontendUri();
            }
            if ( $linkData[1] ) {
                $socialicons .= "'".$value->getType()."': { class: '".$value->getType()."', use: true, link: '".$linkData[0]."', extras: 'target=_blank', title: '".$value->getTitle()."'},";
            } else {
                $socialicons .= "'".$value->getType()."': { class: '".$value->getType()."', use: true, link: '".$linkData[0]."', title: '".$value->getTitle()."'},";    
            }            
        }
        $socialmediaAttributes = "
            $(document).ready(function(){
                $.contactButtons({
                  effect  : 'slide-on-scroll',
                  buttons : {".$socialicons."}
                });
            });            
        ";
        // Include JS and CSS files        
        $this->pageRenderer->addCssFile(ExtensionManagementUtility::siteRelPath('spt_socialmedia').'Resources/Public/Css/socialwidget.css');
        $this->pageRenderer->addCssFile(ExtensionManagementUtility::siteRelPath('spt_socialmedia').'Resources/Public/Css/font-awesome/css/font-awesome.min.css');
        if ( $this->settings['includeJSLib'] ) {
            $this->pageRenderer->addJsFile(ExtensionManagementUtility::siteRelPath('spt_socialmedia').'Resources/Public/Js/jquery.min.js', 'text/javascript', '', true);  
        }
        $this->pageRenderer->addJsFooterFile(ExtensionManagementUtility::siteRelPath('spt_socialmedia').'Resources/Public/Js/socialwidget.js');
        $this->pageRenderer->addFooterData('<script type="text/javascript">'.$socialmediaAttributes.'</script>');
        $this->view->assign('socialmedias', $socialmedias);
    }
}