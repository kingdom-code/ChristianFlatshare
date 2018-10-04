<?php

/* emails/login_reminder.html.twig */
class __TwigTemplate_48e29ec0a4a8263cc59ca1fccce7539e extends Twig_Template
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
    <p>You have not logged in to Christian Flatshare for ";
        // line 6
        echo twig_escape_filter($this->env, (isset($context["last_login_days"]) ? $context["last_login_days"] : null), "html", null, true);
        echo " days.</p>
    <p>Please can you confirm if you advert is still needed:</p>
    
    <ul>
        <li><a href=\"";
        // line 10
        echo twig_escape_filter($this->env, (isset($context["keep_url"]) ? $context["keep_url"] : null), "html", null, true);
        echo "\">My advert is still needed</a> (sets your &quot;last logged in&quot; date to today)</li>
        <li><a href=\"";
        // line 11
        echo twig_escape_filter($this->env, (isset($context["suspend_url"]) ? $context["suspend_url"] : null), "html", null, true);
        echo "\">Please remove my advert</a> (suspends your advert so it is no longer shown, stops Flat-Match emails)</li>
    </ul>
    
    <p>Adverts are suspended as assumed no longer needed if a member does not login for 30 days.</p>
    <p>Suspended adverts can be un-suspended at anytime by logging  in and going to &quot;Your ads&quot;.</p>
    
    <p>If the above links do not work in your browser, please cut and paste these into your browser:</p>
    <p>My advert is still needed: ";
        // line 18
        echo twig_escape_filter($this->env, (isset($context["keep_url"]) ? $context["keep_url"] : null), "html", null, true);
        echo "<br />Please remove my advert: ";
        echo twig_escape_filter($this->env, (isset($context["suspend_url"]) ? $context["suspend_url"] : null), "html", null, true);
        echo "</p>
";
    }

    public function getTemplateName()
    {
        return "emails/login_reminder.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  63 => 18,  53 => 11,  49 => 10,  42 => 6,  38 => 5,  31 => 4,  28 => 3,);
    }
}
