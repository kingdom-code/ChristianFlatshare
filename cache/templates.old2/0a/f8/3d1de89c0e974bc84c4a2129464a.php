<?php

/* emails/ad_expiry.html.twig */
class __TwigTemplate_0af83d1de89c0e974bc84c4a2129464a extends Twig_Template
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
        echo "    <p><strong><a href=\"";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["ad"]) ? $context["ad"] : null), "url"), "html", null, true);
        echo "\">";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["ad"]) ? $context["ad"] : null), "title"), "html", null, true);
        echo "</a></strong></p>

    <p><strong>Hi ";
        // line 6
        echo twig_escape_filter($this->env, (isset($context["first_name"]) ? $context["first_name"] : null), "html", null, true);
        echo ",</strong></p>
    <p>Your Christian Flatshare advert expires on <strong>";
        // line 7
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["ad"]) ? $context["ad"] : null), "expiry_date"), "html", null, true);
        echo "</strong>, 10 days after its &quot;";
        echo twig_escape_filter($this->env, (isset($context["date_type"]) ? $context["date_type"] : null), "html", null, true);
        echo "&quot; date.</p>
    <p>To keep your advert published for longer, or to remove your advert, click:</p>
    
    <ul>
        <li><a href=\"";
        // line 11
        echo twig_escape_filter($this->env, (isset($context["keep_url"]) ? $context["keep_url"] : null), "html", null, true);
        echo "\">My advert is still needed</a> (sets your ads &quot;";
        echo twig_escape_filter($this->env, (isset($context["date_type"]) ? $context["date_type"] : null), "html", null, true);
        echo "&quot; and your &quot;last logged in&quot; date to today)</li>
        <li><a href=\"";
        // line 12
        echo twig_escape_filter($this->env, (isset($context["suspend_url"]) ? $context["suspend_url"] : null), "html", null, true);
        echo "\">Please remove my advert</a> (suspends your advert so it is no longer shown, stops Flat-Match emails)</li>
    </ul>
    
    <p>If the above links do not work in your browser, please cut and paste these into your browser:</p>
    <p>My advert is still needed: ";
        // line 16
        echo twig_escape_filter($this->env, (isset($context["keep_url"]) ? $context["keep_url"] : null), "html", null, true);
        echo "<br />Please remove my advert: ";
        echo twig_escape_filter($this->env, (isset($context["suspend_url"]) ? $context["suspend_url"] : null), "html", null, true);
        echo "</p>
";
    }

    public function getTemplateName()
    {
        return "emails/ad_expiry.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  65 => 16,  58 => 12,  52 => 11,  43 => 7,  39 => 6,  31 => 4,  28 => 3,);
    }
}
