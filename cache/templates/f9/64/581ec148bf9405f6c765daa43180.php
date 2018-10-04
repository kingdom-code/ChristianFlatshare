<?php

/* emails/forward.html.twig */
class __TwigTemplate_f964581ec148bf9405f6c765daa43180 extends Twig_Template
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
    <p>From: <strong>";
        // line 5
        echo twig_escape_filter($this->env, (isset($context["name"]) ? $context["name"] : null), "html", null, true);
        echo "</strong><br />Message:</p>
    <p><strong>";
        // line 6
        echo nl2br(twig_escape_filter($this->env, (isset($context["message"]) ? $context["message"] : null), "html", null, true));
        echo "</strong></p>
    ";
        // line 7
        if ((!twig_test_empty((isset($context["from_ads"]) ? $context["from_ads"] : null)))) {
            // line 8
            echo "    <p>Adverts placed by <strong>";
            echo twig_escape_filter($this->env, (isset($context["name"]) ? $context["name"] : null), "html", null, true);
            echo "</strong> currently showing on Christian Flatshare:</p>
    <ul>
    ";
            // line 10
            $context['_parent'] = (array) $context;
            $context['_seq'] = twig_ensure_traversable((isset($context["from_ads"]) ? $context["from_ads"] : null));
            foreach ($context['_seq'] as $context["_key"] => $context["ad"]) {
                // line 11
                echo "        <li><a href=\"";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["ad"]) ? $context["ad"] : null), "url"), "html", null, true);
                echo "\">";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["ad"]) ? $context["ad"] : null), "title"), "html", null, true);
                echo "</a></li>
    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['ad'], $context['_parent'], $context['loop']);
            $context = array_merge($_parent, array_intersect_key($context, $_parent));
            // line 13
            echo "    </ul>
    ";
        }
        // line 15
        echo "    <p class=\"title\"><strong>Being safe online...</strong></p>
    <p>Below are some common sense pointers to observe when interacting with others online, which may help you to spot unusual behaviour.</p>
    
    <ul>
        <li>Any requests for payments through untraceable money transfer services such as &quot;Western Union Money Transfer&quot; or &quot;Moneygram&quot; should be treated as highly suspicious. </li>
        <li><a href=\"http://www.consumerdirect.gov.uk/watch_out/scams/cheque-overpayment/\">Overpayments scams</a>: overpayments made with a request for the 'change' (sometimes blamed on a clerical error) should be treated suspiciously.</li>
        <li>Be wary of anyone who appears to want to remain distant from you, and does not wish to see you or the accommodation before parting with  money.</li>
        <li>Do not disclose your banking information to individuals over the internet.</li>
        <li>Do not send images or copies of identification, such as your driving license or passport, which can be used for identity theft.</li>
        <li>Scammers often refrain from telephone contact as they are often outside the UK.</li>
        <li>Do not sign a contract or make a payment without seeing the accommodation first. Protracted or seemingly awkward situations which make it difficult to see accommodation before paying a deposit or rent should be treated very cautiously.</li>
    </ul>
    
    <p>The <a href=\"http://www.oft.gov.uk/oft_at_work/consumer_initiatives/scams/\">Office of Fair Trading</a> and the <a href=\"http://www.dti.gov.uk/sectors/infosec/infosecadvice/general/fraudsandscams/page33294.html\">DTI</a> provide some helpful information on these matters.</p>
    <p>If your advert has ticked &quot;<strong><em>would prefer someone who if asked could provide a recommendation from a church</em></strong>&quot;, remember that you can always ask for such a church contact (website and email addresses) who can say that the responding  is known to them. This is quite in the spirit of using CFS and you should expect such points of contact within churches to be offered willingly. If you do contact that church, those working in the church's office a very likely to be all too glad to help confirm that the person responding is known to them.</p>
";
    }

    public function getTemplateName()
    {
        return "emails/forward.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  73 => 15,  69 => 13,  58 => 11,  54 => 10,  48 => 8,  46 => 7,  42 => 6,  38 => 5,  31 => 4,  28 => 3,);
    }
}
