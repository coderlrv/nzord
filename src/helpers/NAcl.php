<?php
namespace NZord\Helpers;

use Modulos\System\Models\ModAcesso;


class NAcl{
    protected $app;
    protected $session;
    protected $permissions = null;
    protected $permissionsAlias = [];

    //Fica definiçções de modulos
    protected $permissiosMods = null;

    /**
     *  Construtor NAcl
     *
     * @param \Slim\Container $c
     * @param \Base\Controller\Session $session
     */
    function __construct($c,$session){
        $this->app = $c;
        $this->session = $session;
    }
    //--------------------------------------------------------------------------------
    /**
     * Defini as permissões para rotas dos modulos.
     * Ex:
     *  __construct($app){
     *      $app->acl->->authorize(['deptos'=>[ 4 ] ],['index','novoComInterno','geral','gridJson']);
     * }
     *
     * @param array $permission -  lista de permições por perfil. Ex:
     * ```php
     * $permissions = [
     *      'deptos'=>[]
     *      'setores'=>[]
     *      'perfils'=>[],
     *      'users'=> [1,2],
     * ]
     * ```
     * @param array|string $actions / Alias  - lista de actions a ser protegidas. ou alias para usar
     * @return void
     */
    public function authorize(array $permission,$actions,$alias=''){
       
        if(!$this->permissions){
            $this->permissions = [];
        }
        
        //Caso tive actions e alias entao adicionar nos duas listas.
        if(is_array($actions) && $alias){
            array_push($this->permissions,['permissions' => $permission , 'actions'=> $actions ,'not'=>false]);
            array_push($this->permissionsAlias,['permissions' => $permission , 'alias'=>$alias,'not'=>false]);
        }else if(is_array($actions)){
            array_push($this->permissions,['permissions' => $permission , 'actions'=> $actions,'not'=>false]);
        }else{
            array_push($this->permissionsAlias,['permissions' => $permission , 'alias'=> $actions,'not'=>false]);
        }
        return $this;
    }
    //--------------------------------------------------------------------------------
    public function authorizeMod($modulo,array $actions){
        $this->permissiosMods = [ strtolower($modulo) => $actions ];
        $this->generateDBModAcesso();
        return $this;
    }
    //--------------------------------------------------------------------------------
    /**
     * Geração automatica do modelo de permissões para tabela mod_acesso
     */
    public function generateDBModAcesso(){
        $modulos = $this->permissiosMods;
        foreach ($modulos as $key => $value) {
            $mod        = explode('::',$key);
            $modulo     = $mod[0];
            $controller = $mod[1];
            $sMod       = ModAcesso::where('modulo',$modulo)->whereNull('controller')->first();
            if( !$sMod ){
                $iMod = new ModAcesso();
                $iMod->superior   = 0;
                $iMod->modulo     = $modulo;
                $iMod->descricao  = 'Geração Automatica - Modulo: '.$modulo;
                $iMod->status     = 1;
                $iMod->save();
                $this->generateDBModAcesso();
            }                    
            if($sMod){
                $bMod   = $sMod->id;             
                $sCont  = ModAcesso::where('superior',$bMod)->where('controller',$controller)->first();
                if( !$sCont  ){
                    $iCont = new ModAcesso();
                    $iCont->superior   = $bMod;
                    $iCont->modulo     = $modulo;
                    $iCont->controller = $controller;
                    $iCont->descricao  = 'Geração Automatica - Controller: '.$controller;
                    $iCont->status     = 1;
                    $iCont->save();                    
                    $this->generateDBModAcesso();
                }
                if( $sCont ){
                    $bCont   = $sCont->id;
                    foreach ($value as $val) {          
                        $sAct  = ModAcesso::where('superior',$bCont)->where('action',$val)->first();        
                        if( !$sAct  ){
                            $iAct = new ModAcesso();
                            $iAct->superior   = $bCont;
                            $iAct->modulo     = $modulo;
                            $iAct->controller = $controller;
                            $iAct->action     = $val;
                            $iAct->descricao  = 'Geração Automatica - Action: '.$val;
                            $iAct->status     = 1;
                            $iAct->save();
                        }
                    }                    
                }
            }
        }
    }
    //--------------------------------------------------------------------------------
    /**
     * Defini as permissões para rotas dos modulos.
     * Ex:
     *  __construct($app){
     *      $app->acl->->authorize(['deptos'=>[ 4 ] ],['index','novoComInterno','geral','gridJson']);
     * }
     *
     * @param array $permission -  lista de permições por perfil. Ex:
     * ```php
     * $permissions = [
     *      'deptos'=>[]
     *      'setores'=>[]
     *      'perfils'=>[],
     *      'users'=> [1,2],
     * ]
     * ```
     * @param array|string $actions / Alias  - lista de actions a ser protegidas. ou alias para usar
     * @return void
     */
    public function authorizeNot(array $permission,$actions,$alias=null){
        if(!$this->permissions){
            $this->permissions = [];
        }
        //Caso tive actions e alias entao adicionar nos duas listas.
        if(is_array($actions) && $alias){
            array_push($this->permissions,['permissions' => $permission , 'actions'=> $actions ,'not'=>true]);
            array_push($this->permissionsAlias,['permissions' => $permission , 'alias'=> $alias,'not'=>true]);
        }else if(is_array($actions)){
            array_push($this->permissions,['permissions' => $permission , 'actions'=> $actions ,'not'=>true]);
        }else{
            array_push($this->permissionsAlias,['permissions' => $permission , 'alias'=> $actions,'not'=>true]);
        }

        return $this;
    }
    //--------------------------------------------------------------------------------
    /**
     *  Checa permissão ou permissao com alias 
     * 
     * @param array | string - ['users'=>[],'perfils'=>[]]
     */
    public function can($permissions,$action=null){
      
        if(is_string($permissions) && isset($action)){
            
            $definicao = explode('::',$permissions);
          
            if(count($definicao) < 2 ){
                throw new \Exception('Formato incorreto de permissão. Correto can("modulo::nomeController","permissao")');
            }

            return $this->checkAccessModAction($definicao[0] ,$definicao[1],$action,false);

        }else{

            if(is_array($permissions) || is_numeric($permissions)){
                return $this->checkPermission((array) $permissions);
            }
    
            $perm = array_values(array_filter($this->permissionsAlias,function($item) use ($permissions){
                    return $item['alias'] == $permissions;
                }));
    
            if(!isset($perm[0])){
                return false;
            }
            if($perm[0]['not']){
                return !$this->checkPermission($perm[0]['permissions']);
            }else{
                return $this->checkPermission($perm[0]['permissions']);
            }
        }
    }
    //--------------------------------------------------------------------------------
    /**
     *  Checa permissão ou permissao com alias 
     * 
     * @param array | string
     */
    public function canNot($permissions){
        if(is_array($permissions)){
            return  !$this->checkPermission($permissions);
        }
        
        $perm = array_filter($this->permissionsAlias,function($item) use ($permissions){ return $item['alias'] == $permissions;});
        if(isset($perm[0])){
            return !$this->checkPermission($perm[0]['permissions']);
        }else{
            return !true;
        }
    }
    //--------------------------------------------------------------------------------
    /**
     *  Passa nome action da rota para verificar oque ta definido no construtor do controller; 
     * 
     * @param [string] $action
     * @return void
     */
    public function checkPermissionAction($mod,$controller,$action){
        ///
        //Caso nao for aplicado nenhuma permissao entao deixa liberado.

        if(!$this->permissiosMods){
            if(!$this->permissions){
                return true;
            }

            foreach($this->permissions as $perm){
                //Verifica se possui a rota configurada. Caso nao exista, passe como possui acesso.
                if(in_array($action,$perm['actions'])){
                    // caso ja ache ja retorna porque ja possui acesso.
                    //caso for para ser negativo.
                    if($perm['not']){
                        return !$this->checkPermission($perm['permissions']);
                    }else{
                        return $this->checkPermission($perm['permissions']);
                    }
                }else{
                    return true;
                }
            }
        }else{
            /**
             * Permissões de modulos. Verifica dados com array  de permissoes na sessao userModAccess
             */
            if(!$this->permissiosMods){
                return true;
            }
            
            return $this->checkAccessModAction($mod,$controller,$action);
        }
      
        return false;
    }
    //--------------------------------------------------------------------------------
    /**
     * Checa permissoes na sessao do usuário. 
     *  Depto \ Setor \ Perfil \ Usuario
     * @param [array] $permission
     * @return void
     */
    private function checkPermission($permission){
        extract ($permission, EXTR_PREFIX_SAME, "wddx");
        $permit = false;
        //Checa departamento / organizacao
        if(isset($deptos) && in_array($this->session->get('userDpto'),(array) $deptos)){
            $permit = true;
        }

        //Checa setor
        if(isset($setores) && in_array($this->session->get('userSetor'),(array) $setores)){
            $permit = true;
        }

        //Checa perfils
        if(isset($perfils) && in_array($this->session->get('userPerfilAdic'),(array) $perfils)){
            $permit = true;
        }

        //Checa users
        if(isset($users) && in_array($this->session->get('user'),(array) $users)){
            $permit = true;
        }

        return $permit;
    }
    //--------------------------------------------------------------------------------
    private function checkAccessModAction($mod,$controller,$action,$checkController=true){
        $modAccess = $this->session->get('userModAccess');
        if( !$modAccess ){
            return false;
        }
        //Checa se existe definição no constroller
        if($checkController){
            $nameModule = strtolower($mod).'::'.strtolower($controller);
            $permissionMod = $this->permissiosMods[$nameModule];
            
            //Caso nao exista definição no construtor do controller retornar com acesso
            if(!$permissionMod || !in_array($action,$permissionMod)) return true;
        }
      
        //Verifica se usuário tem acesso ao controller
        $access = array_filter($modAccess,function($item) use ($mod,$controller,$action){
            if(strtolower(trim($item->modulo)) == strtolower(trim($mod))
                && strtolower(trim($item->controller)) == strtolower(trim($controller)) 
                && strtolower(trim($item->action)) == strtolower(trim($action))){
                return true;
            }else{
                return false;
            }
        });
        
        return count($access) == 0 ? false : true;
    }
    //--------------------------------------------------------------------------------
}