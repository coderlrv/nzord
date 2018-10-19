<?php
namespace NZord\Helpers\TwigExtensions;

class NAclTwigExtension extends \Twig_Extension
{
    private $acl;

    public function __construct($acl)
    {
        $this->acl = $acl;
    }

    public function getFunctions()
    {
        return [ 
            new \Twig_SimpleFunction('can', [$this, 'can']), 
            new \Twig_SimpleFunction('canNot', [$this, 'canNot']) 
        ];
    }

    /**
     * Verifica se permissões validas.
     * @param array $pemissoes -  ['users':[1,2],'deptos':[1,2] ]
     * @return void
     */
    public function can($permissions,$action=null)
    {

        return $this->acl->can($permissions,$action=null);
    }

    /**
     *  Verifica se usuário nao estão nos grupos passado por paramentro.
     *  
     * @param array $pemissoes -  ['users':[1,2],'deptos':[1,2] ]
     * @return void
     */
    public function canNot($pemissoes)
    {
        return $this->acl->canNot($pemissoes);
    }
}
