<?php

/* header.twig */
class __TwigTemplate_4827483ed249822352b107b71b1a0929 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<div id=\"logoContainer\">
\t<div id=\"logo\"><a href=\"";
        // line 2
        echo twig_escape_filter($this->env, (isset($context["homeURL"]) ? $context["homeURL"] : null), "html", null, true);
        echo "\"><img src=\"images/logo.gif\" alt=\"Christian Flatshare logo (click to return to home page)\" width=\"462\" height=\"71\" border=\"0\" /></a></div>
\t<div id=\"iconCanvas\">
\t\t<a href=\"";
        // line 4
        echo twig_escape_filter($this->env, (isset($context["homeURL"]) ? $context["homeURL"] : null), "html", null, true);
        echo "\">
\t\t\t<img src=\"images/icon-1-over.gif\" width=\"80\" height=\"60\" border=\"0\" class=\"iconOver\" />
\t\t\t<img src=\"images/icon-1.gif\" width=\"80\" height=\"60\" border=\"0\" />
\t\t\t<div class=\"iconText\">Home page</div>
\t\t</a>
\t\t<a href=\"countries.php\">
\t\t\t<img src=\"images/icon-2-over.gif\" width=\"80\" height=\"60\" border=\"0\" class=\"iconOver\" />
\t\t\t<img src=\"images/icon-2.gif\" width=\"80\" height=\"60\" border=\"0\" />
\t\t\t<div class=\"iconText\">Countries</div>
\t\t</a>
\t\t<a href=\"contact-us.php\">
\t\t\t<img src=\"images/icon-3-over.gif\" width=\"80\" height=\"60\" border=\"0\" class=\"iconOver\" />
\t\t\t<img src=\"images/icon-3.gif\" width=\"80\" height=\"60\" border=\"0\" />
\t\t\t<div class=\"iconText\">Contact us</div>
\t\t</a>
        ";
        // line 19
        if (((isset($context["loggedIn"]) ? $context["loggedIn"] : null) == false)) {
            // line 20
            echo "    \t\t<a href=\"login.php\">
    \t\t\t<img src=\"images/icon-4-over.gif\" width=\"80\" height=\"60\" border=\"0\" class=\"iconOver\" />
    \t\t\t<img src=\"images/icon-4.gif\" width=\"80\" height=\"60\" border=\"0\" />
    \t\t\t<div class=\"iconText\">Register / Login</div>
    \t\t</a>
        ";
        } else {
            // line 26
            echo "    \t\t<a href=\"your-account-manage-posts.php\"> 
    \t\t\t<img src=\"images/icon-my-ads-over.gif\" width=\"80\" height=\"60\" border=\"0\" class=\"iconOver\" />
    \t\t\t<img src=\"images/icon-my-ads.gif\" width=\"80\" height=\"60\" border=\"0\" />
    \t\t\t<div class=\"iconText\">Your ads</div>
    \t\t</a>
\t    ";
        }
        // line 32
        echo "\t</div>\t\t\t\t\t\t\t\t\t
</div>
<a name=\"m\"></a>\t\t
<div class=\"redMenu\">
\t<ul>
\t\t<li><a href=\"about-us.php\">about Christian Flatshare</a></li>
\t\t<li><a href=\"what-is-a-christian.php\">what is a Christian?</a></li>
\t\t<li><a href=\"stories.php\">CFS Stories</a></li>
\t\t<li><a href=\"use-cfs-in-your-church.php\">use CFS in YOUR church</a></li>
        ";
        // line 41
        if (((isset($context["country"]) ? $context["country"] : null) == "GB")) {
            // line 42
            echo "\t\t    <li><a href=\"churches-using-cfs.php?area=Greater%20London#directory\">churches using CFS</a></li>
        ";
        } else {
            // line 44
            echo "            <li><a href=\"churches-using-cfs-intl.php\">churches using CFS</a></li>
        ";
        }
        // line 46
        echo "\t\t<li class=\"noSeparator\"><a href=\"frequently-asked-questions.php\">Frequently Asked Questions</a></li>
\t</ul>
</div>";
    }

    public function getTemplateName()
    {
        return "header.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  84 => 46,  80 => 44,  76 => 42,  74 => 41,  63 => 32,  55 => 26,  47 => 20,  45 => 19,  27 => 4,  22 => 2,  19 => 1,);
    }
}
