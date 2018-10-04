<?php

/* emails/reply.html.twig */
class __TwigTemplate_782447bbe9722a42d20d27cca0d66e62 extends Twig_Template
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
        echo "    ";
        if ((isset($context["advert_owner"]) ? $context["advert_owner"] : null)) {
            // line 5
            echo "        <p><strong><a href=\"";
            echo twig_escape_filter($this->env, (isset($context["suspend_url"]) ? $context["suspend_url"] : null), "html", null, true);
            echo "\">Suspend your advert</a></strong> when it is no longer needed.</p>
    ";
        }
        // line 7
        echo "
    <p>Regarding: <strong><a href=\"";
        // line 8
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["advert"]) ? $context["advert"] : null), "url"), "html", null, true);
        echo "\">";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["advert"]) ? $context["advert"] : null), "title"), "html", null, true);
        echo "</a></strong></p>
    
    <p><strong>";
        // line 10
        echo twig_escape_filter($this->env, (isset($context["name"]) ? $context["name"] : null), "html", null, true);
        echo "</strong> has sent you a message.</p>
    <p><strong><a href=\"";
        // line 11
        echo twig_escape_filter($this->env, (isset($context["msg_url"]) ? $context["msg_url"] : null), "html", null, true);
        echo "\">Read their message</a></strong> (this will also set your &quot;last login&quot; date to today)</p>

    ";
        // line 13
        if ((!twig_test_empty((isset($context["from_ads"]) ? $context["from_ads"] : null)))) {
            // line 14
            echo "    <p>Adverts placed by <strong>";
            echo twig_escape_filter($this->env, (isset($context["name"]) ? $context["name"] : null), "html", null, true);
            echo "</strong> currently showing on Christian Flatshare:</p>
    <ul>
    ";
            // line 16
            $context['_parent'] = (array) $context;
            $context['_seq'] = twig_ensure_traversable((isset($context["from_ads"]) ? $context["from_ads"] : null));
            foreach ($context['_seq'] as $context["_key"] => $context["ad"]) {
                // line 17
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
            // line 19
            echo "    </ul>
    ";
        }
        // line 21
        echo "    
    <p>To help other Christian Flatshare members with their accommodation needs, please share Christian Flatshare with your friends and church leadership.</p>
    
    <p class=\"title\"><strong>Being safe online...</strong></p>
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
        return "emails/reply.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  86 => 21,  82 => 19,  71 => 17,  67 => 16,  61 => 14,  59 => 13,  54 => 11,  50 => 10,  43 => 8,  40 => 7,  34 => 5,  31 => 4,  28 => 3,);
    }
}
