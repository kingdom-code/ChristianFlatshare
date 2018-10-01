<?php

/* emails/base.html.twig */
class __TwigTemplate_f816884e565c8cb63550f6cd29a68dab extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'body' => array($this, 'block_body'),
            'footer' => array($this, 'block_footer'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
        <title>Christian Flatshare</title>
    </head>
    <body>
        <table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">
            <tr width=\"100%\">
                <td align=\"center\" width=\"100%\">
                    <table width=\"600\" cellspacing=\"0\" cellpadding=\"0\" class=\"inner\">
                        <tr>
                            <td class=\"header\" align=\"left\">
                                <a href=\"http://www.christianflatshare.org\"><img src=\"http://www.christianflatshare.org/images/logo.gif\" alt=\"(Christian Flatshare logo)\" width=\"462\" height=\"71\" border=\"0\" /></a>
                            </td>
                        </tr>
                        <tr>
                            <td class=\"body\" align=\"left\">
                                ";
        // line 19
        $this->displayBlock('body', $context, $blocks);
        // line 20
        echo "                                <p class=\"foot first\"><em>Christian Flatshare... helping accommodation seekers connect with the local church community</em></p>
                                <p class=\"foot\"><em>Finding homes, growing churches and building communities</em></p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" class=\"divider\">
            <tr width=\"100%\">
                <td align=\"center\" width=\"100%\">
                    <table width=\"600\" cellspacing=\"0\" cellpadding=\"0\" class=\"inner\">
                        <tr>
                            <td class=\"footer\">
                                ";
        // line 34
        $this->displayBlock('footer', $context, $blocks);
        // line 58
        echo "                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>";
    }

    // line 19
    public function block_body($context, array $blocks = array())
    {
    }

    // line 34
    public function block_footer($context, array $blocks = array())
    {
        // line 35
        echo "                                    <p><strong>About  Christian Flatshare...</strong></p>
                                    <p>Christian Flatshare (CFS) is a non-profit organisation dedicated to helping accommodation seekers connect with the local church community: finding homes, growing churches and building communities. CFS gives all profits to charity. </p>
                                    <p>CFS wants to help the church to be, in accommodation terms, a welcoming place... <em>everyone</em> needs a place to stay.<p>
                                    <p>CFS helps  accommodation seekers to connect with the local church community, and for church fellowships to connect with those moving to their area. </p>
                                    <p>CFS is supported by the leadership of hundreds of churches nationwide. Church, Christian organisation and student group leaders can support CFS by allowing CFS to include them in CFS's Church Directory and to represent them on the accommodation maps with a link to their website.</p>
                                    <p>CFS is  for accommodation for the whole church family:</p>
                                    <ul>
                                        <li> Flat / House Share (a flat or house shared with others at a similar age or stage in  life)</li>
                                        <li>Room Share (a room shared with someone of the same sex)</li>
                                        <li> Family  Share (live with a family or a married couple)</li>
                                        <li> Whole  Place (an unoccupied flat or house)</li>
                                    </ul>
                                    <p>CFS features:
                                    <ul>
                                        <li> Wanted and offered accommodation advert, nation-wide </li>
                                        <li> Automatic &ldquo;Flat-Match&rdquo; emails, to receive email with suitable accommodation matches </li>
                                        <li>Search  results displayed on a map </li>
                                        <li>&quot;Pal-Up&quot; option to find someone to look for accommodation together with</li>
                                        <li> Secure  email correspondence through the site</li>
                                        <li>and  lots more cool stuff, plus some lovely graphics...</li>
                                    </ul>
                                    <p>Christian Flatshare is free to use. Please help others using CFS by sharing it with your church. </p>
                                ";
    }

    public function getTemplateName()
    {
        return "emails/base.html.twig";
    }

    public function getDebugInfo()
    {
        return array (  80 => 35,  77 => 34,  72 => 19,  61 => 58,  43 => 20,  41 => 19,  21 => 1,  66 => 16,  59 => 34,  53 => 11,  44 => 7,  40 => 6,  36 => 5,  31 => 4,  28 => 3,);
    }
}
