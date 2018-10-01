<?php

/* emails/photo_reminder_offered.html.twig */
class __TwigTemplate_2f75f619e1fed2c7c1b6adc191522ab9 extends Twig_Template
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
        echo "    <p class=\"title\"><strong>Serious about your advert? Add photos...</strong></p>
    
    <p>Photos can help others to see what the accommodation is like, and can help to introduce the household.</p>
    <p>Adverts with photos (and informative descriptions) usually have a <strong>much</strong> better response - and photos can be fun!</p>
    
    <p>To add photos, click &quot;Add photos&quot; (Note: this is an example advert.)</p>
    
    <img src=\"";
        // line 11
        echo twig_escape_filter($this->env, (isset($context["image_url"]) ? $context["image_url"] : null), "html", null, true);
        echo "\" width=\"540\" height=\"331\" />
";
    }

    public function getTemplateName()
    {
        return "emails/photo_reminder_offered.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  40 => 11,  31 => 4,  28 => 3,);
    }
}
