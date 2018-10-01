<?php

/* emails/login_suspend_ad.html.twig */
class __TwigTemplate_aff08c1a3d17c13bcccdb7821c00731e extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = $this->env->loadTemplate("emails/base.html.twig");

        $this->blocks = array(
            'body' => array($this, 'block_body'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "emails/base.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_body($context, array $blocks = array())
    {
        // line 4
        echo "    <p>Regarding: <strong><a href=\"";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["advert"]) ? $context["advert"] : null), "url"), "html", null, true);
        echo "\">";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["advert"]) ? $context["advert"] : null), "title"), "html", null, true);
        echo "</a></strong></p>
    <p>Hi ";
        // line 5
        echo twig_escape_filter($this->env, (isset($context["first_name"]) ? $context["first_name"] : null), "html", null, true);
        echo ",</p>
    <p>We have suspended your advert as you have not logged in to Christian Flatshare for ";
        // line 6
        echo twig_escape_filter($this->env, (isset($context["last_login_days"]) ? $context["last_login_days"] : null), "html", null, true);
        echo " days.</p>
    <p>If your advert is no longer needed no further action is needed.</p>
    
    <p>To un-suspend your ad click on the link below:</p>
    
    <ul>
        <li><a href=\"";
        // line 12
        echo twig_escape_filter($this->env, (isset($context["unsuspend_url"]) ? $context["unsuspend_url"] : null), "html", null, true);
        echo "\">My advert is still needed</a> (un-suspends your advert)</li>
    </ul>
    
    <p>Adverts are suspended as assumed no longer needed if a member does not login for 30 days.</p>
    <p>Suspended adverts can be un-suspended at anytime by logging  in and going to &quot;Your ads&quot;.</p>
    <p>Logging into Christian Flatshare periodically helps to indicate to others that you are still using Christian Flatshare, as the number of days since you last logged is shown on your advert.</p>
    <p>If the above links do not work in your browser, please cut and paste these into your browser:</p>
    <p>My advert is still needed: ";
        // line 19
        echo twig_escape_filter($this->env, (isset($context["unsuspend_url"]) ? $context["unsuspend_url"] : null), "html", null, true);
        echo "</p>
";
    }

    public function getTemplateName()
    {
        return "emails/login_suspend_ad.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  61 => 19,  51 => 12,  63 => 18,  53 => 11,  49 => 10,  42 => 6,  38 => 5,  31 => 4,  28 => 3,);
    }
}
