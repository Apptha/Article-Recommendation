<?php
/**
 * @version		$Id: articlerecommentation.php revision date lasteditedby $
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );
jimport( 'joomla.application.application' );
jimport( 'joomla.html.parameter' );
global $classid ;
$classid = 1;
class plgContentArticlerecommentation extends JPlugin {
    
    function plgContentArticlerecommentation( &$subject, $params )
    {
                $doc =& JFactory::getDocument();
                $this->params =  &$params;

                $plugin = JPluginHelper::getPlugin('content', 'articlerecommentation');
                $paramcollection = new JParameter($plugin->params);

                if(version_compare(JVERSION,'1.6.0','ge')) {
                    $doc->addScript(JURI::base()."plugins/content/articlerecommentation/articleincludes/jquery.js" );
                    $doc->addScript(JURI::base()."plugins/content/articlerecommentation/articleincludes/recommentation.js" );

                    if($paramcollection->def("fblike") == '1')
                    $doc->addScript(JURI::base()."plugins/content/articlerecommentation/articleincludes/facebook.js" );

                    $doc->addStyleSheet(JURI::base()."plugins/content/articlerecommentation/articleincludes/recommentation.css" );
                }
                else{
                    $doc->addScript(JURI::base()."plugins/content/articleincludes/jquery.js" );
                    $doc->addScript(JURI::base()."plugins/content/articleincludes/recommentation.js" );

                    if($paramcollection->def("fblike") == '1')
                    $doc->addScript(JURI::base()."plugins/content/articleincludes/facebook.js" );

                    $doc->addStyleSheet(JURI::base()."plugins/content/articleincludes/recommentation.css" );
                }
		parent::__construct( $subject, $params );

    }


     function onBeforeDisplayContent( &$article, &$params, $limitstart )
    {
         global $classid;
         global $mainframe;
         $classid++;

         if(isset( $article->text))
            $article->text = "<dfn style='float:left' id='appthacontentWrap$classid' >". $article->text."</dfn>";

         
         $article->text .="<input type='hidden' id='floatboxDirection' value='".$this->params->def('direction')."' />";

    }
    
    //for joomla 1.6 & 1.7
    function onContentBeforeDisplay($context, &$article, &$params, $limitstart){
         global $classid;
         global $mainframe;
         $classid++;


         if(isset( $article->text))
            $article->text = "<dfn style='float:left'  id='appthacontentWrap$classid' >". $article->text."</dfn>";

         $article->text .="<input type='hidden' id='floatboxDirection' value='".$this->params->def('direction')."' />";
    }

    function onAfterDisplayContent( &$article, &$params, $limitstart )
    {
          global $mainframe;

         $categoryId = $article->catid;
         $currentArticleId = $article->id;
         $currentOrdering = (int)$this->getCurrentArticleOrdering($article);
         if($this->checkOrderingAvailable($categoryId,$currentOrdering+1) == "0"){
             $currentOrdering = $this->getnextAvailableOrdering($categoryId,$currentOrdering);
             $currentOrdering--;
         }
         if($currentOrdering <=0)
             $currentOrdering = 0;
         //get next article value;
          $article->text .= $this->getNextArticleId($categoryId,$currentOrdering+1,$params,$article);
    }
    //for joomla 1.6 & 1.7
    function onContentAfterDisplay($context, &$article, &$params, $limitstart)
    {
         global $mainframe;
                
         $categoryId = $article->catid;
         $currentArticleId = $article->id;
         $currentOrdering = (int)$this->getCurrentArticleOrdering($article);
         if($this->checkOrderingAvailable($categoryId,$currentOrdering+1) == "0"){
             $currentOrdering = $this->getnextAvailableOrdering($categoryId,$currentOrdering);
             $currentOrdering--;
         }
         if($currentOrdering <=0)
             $currentOrdering = 0;
         //get next article value;
          $article->text .= $this->getNextArticleId($categoryId,$currentOrdering+1,$params,$article);
    }
    //User defined function starts
    
    function getCurrentArticleOrdering($article){
        $categoryId = $article->catid;
        $currentArticleId = $article->id;
        //get current article ordering value
        $db =& JFactory::getDBO();
        $query = "SELECT ordering FROM #__content WHERE catid = '$categoryId' and id='$currentArticleId' and state='1'  ";
        $db->setQuery($query);
        $result = $db->loadAssocList();

        $currentArticleOrdering = 0;
        if(isset($result[0]["ordering"]))
            $currentArticleOrdering  = $result[0]["ordering"];

        return $currentArticleOrdering;
    }
    function getNextArticleId($categoryId,$currentOrdering,$params,$article){

        $maxOrdering = $this->getMaxOrdering($categoryId);

        $key  = $this->ecom_generate($this->params->def('powered'));

        if($maxOrdering == ($currentOrdering-1) ){
            $currentOrdering = $this->getMinOrdering($categoryId);
        }

        
        
        global $classid;
        $db =& JFactory::getDBO();
        $query = "SELECT * FROM #__content WHERE catid = '$categoryId' and ordering='$currentOrdering'   ";
        $db->setQuery($query);
        $result = $db->loadAssocList();
        $html = '';
        $html .= "<input type='hidden' id='articleCount' value='".$classid."' />";
              
        if(isset($result[0]) ){
            
            $articleItemId = $this->getArticleItemId($result[0]["id"],$result[0]["catid"],$result[0]["sectionid"]);

            if($result[0]["state"]=="1"){

                $needles = array(
                    'article'  => (int) $result[0]["id"],
                    'category' => (int) $result[0]["catid"],
                    'section'  => (int) $result[0]["sectionid"],
                );

                
                $html .= '<div id="floating-box" class="floatboxrelative">';

                if(version_compare(JVERSION,'1.6.0','ge')) {
                    $html .= '<p id="popupCloseBox" style="padding-top:-10px" align="right"><img onclick="stopPopup()" src="'.JURI::base().'plugins/content/articlerecommentation/articleincludes/close.png"</p>';
                }
                else{
                    $html .= '<p id="popupCloseBox" style="padding-top:-10px" align="right"><img onclick="stopPopup()" src="'.JURI::base().'plugins/content/articleincludes/close.png"</p>';
                }
               
                $html .= "<ul style='margin-top:-0px;margin-left:0px;margin-right: 25px;' id='floating-boxul'>";
                $html .= "<li style='margin-left:5px!important;margin-right:25px' id='readNext'>READ NEXT</li>";
                if($this->params->def('fblike')!='0'){
                    $html .= ' <li class="socialLink"><iframe src="http://www.facebook.com/plugins/like.php?href='.urlencode(JURI::base().ContentHelperRoute::getArticleRoute($result[0]["id"],$result[0]["catid"])).'&amp;layout=button_count&amp;show_faces=false&amp;width=450&amp;action=like&amp;colorscheme=light&amp;height=21" scrolling="no" frameborder="0" style="border: none; overflow: hidden; width: 100px; height: 25px;" allowTransparency="true" > </iframe></li>';
                }
                if($this->params->def('twitter')!='0')
                    $html .= '<li class="socialLink"><a href="https://twitter.com/share" class="twitter-share-button" data-url="'.JURI::base().ContentHelperRoute::getArticleRoute($result[0]["id"],$result[0]["catid"]).'">Tweet</a><script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></li>';
                $html .= "</ul>";
                

                if(strlen($result[0]["title"]) >= 36){
                    $result[0]["title"] = substr($result[0]["title"],0,36)."..";
                }
                $html .= '<p style="line-height:0px"><a id="articleId" href="'.ContentHelperRoute::getArticleRoute($result[0]["id"],$result[0]["catid"]).'">'.$result[0]["title"].'</a></p>';

                $result[0]["introtext"] = strip_tags($result[0]["introtext"]);
                if(strlen($result[0]["introtext"])<=105)
                    $html .= '<p onclick="readMore()" style="cursor:pointer">'.$result[0]["introtext"].' <font size="1">Read More</font></p>';
                else
                    $html .= '<p onclick="readMore()" style="cursor:pointer">'.substr($result[0]["introtext"],0,105).'.. <font size="1">Read More</font></p>';

                
                if($key==0)
                    $html .= '<p class="poweredby">'.$this->apiKey().'</p>';

                $html .= '</div>';
            }
            else{
                $currentOrdering++;
                $query = "SELECT * FROM #__content WHERE catid = '$categoryId' and ordering='$currentOrdering' and state='1'  ";
                $db->setQuery($query);
                $result = $db->loadAssocList();
                if(isset($result[0]) ){

                    $html .= '<div id="floating-box"  class="floatboxrelative">';

                if(version_compare(JVERSION,'1.6.0','ge')) {
                    $html .= '<p id="popupCloseBox" style="padding-top:-10px" align="right"><img onclick="stopPopup()" src="'.JURI::base().'plugins/content/articlerecommentation/articleincludes/close.png"</p>';
                }
                else{
                    $html .= '<p id="popupCloseBox" style="padding-top:-10px" align="right"><img onclick="stopPopup()" src="'.JURI::base().'plugins/content/articleincludes/close.png"</p>';
                }

                $html .= "<ul style='margin-top:-0px;margin-left:0px;margin-right: 25px;height:30px' id='floating-boxul'>";
                $html .= "<li style='margin-left:5px;margin-right:25px' id='readNext'>READ NEXT</li>";
                if($this->params->def('fblike')!='0'){
                    $html .= ' <li class="socialLink"><iframe src="http://www.facebook.com/plugins/like.php?href='.urlencode(JURI::base().ContentHelperRoute::getArticleRoute($result[0]["id"],$result[0]["catid"])) .'&amp;layout=button_count&amp;show_faces=false&amp;width=450&amp;action=like&amp;colorscheme=light&amp;height=21" scrolling="no" frameborder="0" style="border: none; overflow: hidden; width: 100px; height: 25px;" allowTransparency="true" > </iframe></li>';
                }
                if($this->params->def('twitter')!='0')
                    $html .= '<li class="socialLink"><a href="https://twitter.com/share" class="twitter-share-button" data-url="'.JURI::base().ContentHelperRoute::getArticleRoute($result[0]["id"],$result[0]["catid"]).'">Tweet</a><script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></li>';
                $html .= "</ul>";


                if(strlen($result[0]["title"]) >= 36){
                    $result[0]["title"] = substr($result[0]["title"],0,36)."..";
                }
                $html .= '<p><a href="'.ContentHelperRoute::getArticleRoute($result[0]["id"],$result[0]["catid"]).'">'.$result[0]["title"].'</a></p>';
                $result[0]["introtext"] = strip_tags($result[0]["introtext"]);
                    if(strlen($result[0]["introtext"])<=105)
                        $html .= '<p onclick="readMore()" style="cursor:pointer">'.$result[0]["introtext"].' <font size="1">Read More</font></p>';
                    else
                        $html .= '<p onclick="readMore()" style="cursor:pointer">'.strip_tags (substr($result[0]["introtext"],0,105)).'.. <font size="1">Read More</font></p>';

                    if($key==0)
                        $html .= '<p class="poweredby">'.$this->apiKey().'</p>';
                    
                    $html .= '</div>';
                }
            }
        }
        $html .= "<div id='fb-root'></div>";
        return $html;
    }
    function getMaxOrdering($categoryId){
        $db =& JFactory::getDBO();
        $query = "SELECT ordering FROM #__content WHERE catid = '$categoryId'  order by ordering desc ";
        $db->setQuery($query);
        $result = $db->loadRow();
        return $result[0];
    }
    function getMinOrdering($categoryId){
        $db =& JFactory::getDBO();
        $query = "SELECT ordering FROM #__content WHERE catid = '$categoryId' and ordering >= 1   order by ordering asc ";
        $db->setQuery($query);
        $result = $db->loadRow();
        return $result[0];
    }
    function isArticleEnabled($articleId){
        $db =& JFactory::getDBO();
        $query = "SELECT state FROM #__content WHERE id= '$articleId'  ";
        $db->setQuery($query);
        $result = $db->loadRow();
        return $result[0];
    }
    function ecom_generate($userApiEcom){
	$domainName = JURI::base();

	//$domainName = "http://iseofirm.net";
        
        $domainName =    $this->get_domain($domainName);

        $strDomainName = strtoupper($domainName);



        $apiecom = $this->ecomBuildInFunction($strDomainName);
        $apiecom = substr($apiecom,0,25)."CONTUS";

        
        if(($userApiEcom !='') && ($userApiEcom == $apiecom))
        {
            return 1;
        }
        else{
            return 0;
        }
    }
    function get_domain($url)
    {
      $pieces = parse_url($url);
      $domain = isset($pieces['host']) ? $pieces['host'] : '';
      if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
        return $regs['domain'];
      }
      return false;
    }
    function ecomBuildInFunction($val)
    {
        $message = "EJ-ARMP0EFIL9XEV8YZAL7KCIUQ6NI5OREH4TSEB3TSRIF2SI1ROTAIDALG-JW";
        $chars_str = 'WJ-GLADIATOR1IS2FIRST3BEST4HERO5IN6QUICK7LAZY8VEX9LIFEMP0';

        $geffencryption = $this->ecomEncryption($chars_str);
        return $this->encrypt($val, $message, $geffencryption, $chars_str);
    }
    function ecomEncryption($chars_str)
    {
        $chars_array = $chars_str;
        for ($i=0;$i<57;$i++) {
            $lookupObj[$this->utfCharToNumber($chars_str[$i])] = $i;
        }
        return $lookupObj;
    }
    function encrypt($tkey, $message, $lookupObj, $chars_str)
    {
        $key_array = $tkey;
        $enc_message = "";
        $kPos = 0;
        for ($i = 0; $i< strlen($message); $i++) {
            $char = $message[$i];
            $offset = $this->getOffset($key_array[$kPos], $char,$lookupObj,$chars_str);

            $enc_message .= $chars_str[$offset];
            $kPos++;
            if ($kPos>=strlen($key_array)) {
                $kPos = 0;
            }
        }
        return $enc_message;
    }
    function getOffset($start, $end, $lookupObj,$chars_array)
    {
        $i=0;
        $eNum = 0;
        $sNum = 0;
        if(isset($lookupObj[$this->utfCharToNumber($start[$i])]))
            $sNum = $lookupObj[$this->utfCharToNumber($start[$i])];
        if($lookupObj[$this->utfCharToNumber($end)])
            $eNum = $lookupObj[$this->utfCharToNumber($end)];
        $offset = ($eNum)-$sNum;
        if ($offset<0) {
            $offset = strlen($chars_array)+($offset);
        }
        return $offset;
    }
    function utfCharToNumber($char)
    {
        $i = 0;
        $number = '';
        while (isset($char{$i})) {
            $number.= ord($char{$i});
            ++$i;
        }
        return $number;
    }
    function apiKey() {
        $this->_const = "wk2A7KrQDNlNbYC5dIUPcIcQn43I7oMGPbAOo6tY0ixC801XYqeUP0+ODLabO2X47f4L0vO0xTt1zebQd/jNV2UzZrz7DGsJ3ykUcEtun7I=";
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $const = base64_decode($this->_const);
        $val = "";
        $val =  mcrypt_decrypt(MCRYPT_RIJNDAEL_128, "macdock", $const, MCRYPT_MODE_ECB, $iv);
        return $val;
    }
    function getArticleItemId($id,$catid,$sectionid){
         $needles = array(
                    'article'  => (int) $id,
                    'category' => (int) $catid,
                    'section'  => (int) $sectionid,
                );

         
        //$item   = ContentHelperRoute::_findItem($needles);
         $itemarray = explode("Itemid=",JRoute::_(ContentHelperRoute::getArticleRoute($needles)));
         
         $articleItemId = 0;
         if(isset ($itemarray[1]))
            $articleItemId = $itemarray[1];

        
        //$articleItemId =  is_object($item) ? $item->id : null;
        return  $articleItemId;
    }
    function checkOrderingAvailable($categoryId,$ordering){
        $db =& JFactory::getDBO();
        $query = "SELECT count(ordering) FROM #__content WHERE catid = '$categoryId' and ordering='$ordering' and state='1'  ";
        $db->setQuery($query);
        $result = $db->loadRow();

        if(isset($result[0])){
            return $result[0];
        }
        else{
            return 0;
        }
    }
    function getnextAvailableOrdering($categoryId,$ordering){
        $db =& JFactory::getDBO();
        $query = "SELECT ordering FROM #__content WHERE catid = '$categoryId' and ordering > '$ordering' and state='1' order by ordering asc   ";
        $db->setQuery($query);
        $result = $db->loadRow();
        if(isset($result[0])){
            return $result[0];
        }
        else{
            return 0;
        }
    }
    
}
?>
