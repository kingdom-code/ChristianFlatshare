<?php

/* emails/forgotten_password.html.twig */
class __TwigTemplate_6582747d70552dbfed92f23605e9c1d5 extends Twig_Template
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
        echo "    <p><strong>Hi ";
        echo twig_escape_filter($this->env, (isset($context["first_name"]) ? $context["first_name"] : null), "html", null, true);
        echo ",</strong></p>

    <p><a href=\"";
        // line 6
        echo twig_escape_filter($this->env, (isset($context["base_url"]) ? $context["base_url"] : null), "html", null, true);
        echo "reset-password.php?token=";
        echo twig_escape_filter($this->env, (isset($context["password"]) ? $context["password"] : null), "html", null, true);
        echo "&amp;email=";
        echo twig_escape_filter($this->env, (isset($context["email"]) ? $context["email"] : null), "html", null, true);
        echo "\">Click here to reset your password</a></p>
";
    }

    public function getTemplateName()
    {
        return "emails/forgotten_password.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  37 => 6,  31 => 4,  28 => 3,);
    }
}
