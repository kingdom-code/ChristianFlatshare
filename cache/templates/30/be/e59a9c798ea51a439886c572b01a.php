<?php

/* emails/send_to_a_friend.html.twig */
class __TwigTemplate_30bee59a9c798ea51a439886c572b01a extends Twig_Template
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
        echo "    <p>A message from your friend <a href=\"mailto:";
        echo twig_escape_filter($this->env, (isset($context["email"]) ? $context["email"] : null), "html", null, true);
        echo "\">";
        echo twig_escape_filter($this->env, (isset($context["name"]) ? $context["name"] : null), "html", null, true);
        echo "</a>:</p>
    <p><strong>";
        // line 5
        echo nl2br(twig_escape_filter($this->env, (isset($context["message"]) ? $context["message"] : null), "html", null, true));
        echo "</strong></p>
    <p>They would like to show you this advert:</p>
    <p><a href=\"";
        // line 7
        echo twig_escape_filter($this->env, (isset($context["link"]) ? $context["link"] : null), "html", null, true);
        echo "\">";
        echo twig_escape_filter($this->env, (isset($context["advert"]) ? $context["advert"] : null), "html", null, true);
        echo "</a></p>
";
    }

    public function getTemplateName()
    {
        return "emails/send_to_a_friend.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  43 => 7,  38 => 5,  31 => 4,  28 => 3,);
    }
}
