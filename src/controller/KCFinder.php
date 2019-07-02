<?php
namespace NZord\Controller;

use Slim\Http\Request;
use Slim\Http\Response;
use kcfinder\browser;

/**
 *  Controller por modulos.
 */
class KCFinderController extends Controller
{
    public function index(Request $request, Response $response, $args=null){
        require_once 'events.php';

        $config = [];
        $app = new \RFM\Application();

        // uncomment to use events
        $app->registerEventsListeners();

        $local = new \RFM\Repository\Local\Storage($config);

        $config = $this->app->get('settings')['kcfinder'];
        $local->setRoot($config['uploadURL'], true, false);

        $app->setStorage($local);

        // set application API
        $app->api = new \RFM\Api\LocalApi();

        $app->run();

    }

    public function browser(Request $request, Response $response, $args=null){

        $this->app->view->render($this->app->response, 'kcfinder/index.html.twig');
    }

    public function config(Request $request, Response $response, $args=null){
        echo '{
            "_comment": "IMPORTANT : go to the wiki page to know about options configuration https://github.com/servocoder/RichFilemanager/wiki/Configuration-options",
            "options": {
                "theme": "flat-dark",
                "showTitleAttr": false,
                "showConfirmation": true,
                "browseOnly": false,
                "fileSorting": "NAME_ASC",
                "folderPosition": "bottom",
                "quickSelect": false,
                "logger": false,
                "allowFolderDownload": true,
                "allowChangeExtensions": false,
                "capabilities": [
                    "select",
                    "upload",
                    "download",
                    "rename",
                    "copy",
                    "move",
                    "delete",
                    "extract",
                    "createFolder"
                ]
            },
            "language": {
                "default": "en",
                "available": ["ar", "bs", "ca", "cs", "da", "de", "el", "en", "es", "fa", "fi", "fr", "he", "hu", "it", "ja", "nl", "pl", "pt", "ru", "sv", "th", "tr", "vi", "zh-CN", "zh-TW"]
            },
            "formatter": {
                "datetime": {
                    "skeleton": "yMMMdHm"
                }
            },
            "filetree": {
                "enabled": true,
                "foldersOnly": false,
                "reloadOnClick": true,
                "expandSpeed": 200,
                "showLine": true,
                "width": 200,
                "minWidth": 200
            },
            "manager": {
                "defaultViewMode": "grid",
                "dblClickOpen": false,
                "selection": {
                    "enabled": true,
                    "useCtrlKey": true
                },
                "renderer": {
                    "position": false,
                    "indexFile": "readme.md"
                }
            },
            "api": {
                "lang": "php",
                "connectorUrl": false,
                "requestParams": {
                    "GET": {},
                    "POST": {},
                    "MIXED": {}
                }
            },
            "upload": {
                "multiple": true,
                "maxNumberOfFiles": 5,
                "chunkSize": false
            },
            "clipboard": {
                "enabled": true,
                "encodeCopyUrl": true
            },
            "filter": {
                "image": ["jpg", "jpeg", "gif", "png", "svg"],
                "media": ["ogv", "avi", "mkv", "mp4", "webm", "m4v", "ogg", "mp3", "wav"],
                "office": ["txt", "pdf", "odp", "ods", "odt", "rtf", "doc", "docx", "xls", "xlsx", "ppt", "pptx", "csv", "md"],
                "archive": ["zip", "tar", "rar"],
                "audio": ["ogg", "mp3", "wav"],
                "video": ["ogv", "avi", "mkv", "mp4", "webm", "m4v"]
            },
            "search": {
                "enabled": true,
                "recursive": false,
                "caseSensitive": false,
                "typingDelay": 500
            },
            "viewer": {
                "absolutePath": true,
                "previewUrl": false,
                "image": {
                    "enabled": true,
                    "lazyLoad": true,
                    "showThumbs": true,
                    "thumbMaxWidth": 64,
                    "extensions": [
                        "jpg",
                        "jpe",
                        "jpeg",
                        "gif",
                        "png",
                        "svg"
                    ]
                },
                "video": {
                    "enabled": true,
                    "extensions": [
                        "ogv",
                        "mp4",
                        "webm",
                        "m4v"
                    ],
                    "playerWidth": 400,
                    "playerHeight": 222
                },
                "audio": {
                    "enabled": true,
                    "extensions": [
                        "ogg",
                        "mp3",
                        "wav"
                    ]
                },
                "iframe": {
                    "enabled": true,
                    "extensions": [
                        "htm",
                        "html"
                    ],
                    "readerWidth": "95%",
                    "readerHeight": "600"
                },
                "opendoc": {
                    "enabled": true,
                    "extensions": [
                        "pdf",
                        "odt",
                        "odp",
                        "ods"
                    ],
                    "readerWidth": "640",
                    "readerHeight": "480"
                },
                "google": {
                    "enabled": true,
                    "extensions": [
                        "doc",
                        "docx",
                        "xls",
                        "xlsx",
                        "ppt",
                        "pptx"
                    ],
                    "readerWidth": "640",
                    "readerHeight": "480"
                },
                "codeMirrorRenderer": {
                    "enabled": true,
                    "extensions": [
                        "txt",
                        "csv"
                    ]
                },
                "markdownRenderer": {
                    "enabled": true,
                    "extensions": [
                        "md"
                    ]
                }
            },
            "editor": {
                "enabled": true,
                "theme": "default",
                "lineNumbers": true,
                "lineWrapping": true,
                "codeHighlight": true,
                "matchBrackets": true,
                "extensions": [
                    "html",
                    "txt",
                    "csv",
                    "md"
                ]
            },
            "customScrollbar": {
                "enabled": true,
                "theme": "inset-2-dark",
                "button": true
            },
            "extras": {
                "extra_js": [],
                "extra_js_async": true
            },
            "url": "https://github.com/servocoder/RichFilemanager",
            "version": "2.7.6"
        }';
    }

    public function language(Request $request, Response $response, $args=null){
        echo '{
            "ALLOWED_FILE_TYPE": "Somente os seguintes arquivos são aceitos: %s",
            "AUTHORIZATION_REQUIRED": "Sem permissão para usar o gerenciador.",
            "DIRECTORY_ALREADY_EXISTS": "A pasta \' %s \' já existe.",
            "DIRECTORY_EMPTY": "O diretório \' %s \' está vazio.",
            "DIRECTORY_NOT_EXIST": "A pasta %s não existe.",
            "DISALLOWED_FILE_TYPE": "Os seguintes arquivos não são aceitos: %s",
            "ERROR_CONFIG_FILE": "Erro no arquivo de configuração: %s",
            "ERROR_COPYING_DIRECTORY": "Erro ao copiar o diretório %s para %s.",
            "ERROR_COPYING_FILE": "Erro ao copiar o arquivo %s para %s.",
            "ERROR_CREATING_ZIP": "Erro ao criar o arquivo zip",
            "ERROR_DELETING_FILE": "Error while deleting the file %s",
            "ERROR_DELETING_DIRECTORY": "Error while deleting the directory %s",
            "ERROR_EXTRACTING_FILE": "Erro ao extrair itens do arquivo",
            "ERROR_OPENING_FILE": "Erro ao abrir o Arquivo.",
            "ERROR_MOVING_DIRECTORY": "Erro ao mover o diretório %s para %s.",
            "ERROR_MOVING_FILE": "Erro ao mover o arquivo %s para %s.",
            "ERROR_RENAMING_DIRECTORY": "Erro ao renomear uma pasta de %s para %s.",
            "ERROR_RENAMING_FILE": "Erro ao renomear o arquivo de %s para %s.",
            "ERROR_SAVING_FILE": "Erro ao salvar o arquivo.",
            "ERROR_SERVER": "Erro do servidor.",
            "ERROR_UPLOADING_FILE": "Erro ao enviar o arquivo.",
            "ERROR_WRITING_PERM": "Você não tem permissão de gravação no arquivo.",
            "FILE_ALREADY_EXISTS": "O arquivo \'%s\' já existe.",
            "FILE_DOES_NOT_EXIST": "O arquivo %s não existe.",
            "FILE_EMPTY": "O arquivo está vazio",
            "FORBIDDEN_ACTION_DIR": "Não é possível executar ações. Proibido para diretórios.",
            "FORBIDDEN_ACTION_FILE": "Não é possível executar ações. Proibido para arquivos.",
            "FORBIDDEN_CHAR_SLASH": "O uso de \'/\' é proibido no diretório ou arquivo nome.",
            "FORBIDDEN_CHANGE_EXTENSION": "Você não tem permissão para alterar a extensão do arquivo.",
            "FORBIDDEN_NAME": "Nome proibido \' %s \'.",
            "INVALID_ACTION": "Ação inválida.",
            "INVALID_DIRECTORY_PATH": "Directory path \' %s \' is invalid.",
            "INVALID_FILE_PATH": "File path \' %s \' is invalid.",
            "INVALID_SYMLINK_PATH": "Symbolic link \' %s \' has invalid target path.",
            "INVALID_FILE_TYPE": "Tipo de arquivo não aceito.",
            "INVALID_FILE_UPLOAD": "Carregar de arquivo inválido.",
            "INVALID_VAR": "Variável %s inválida.",
            "NOT_FOUND_LANGUAGE_FILE": "Arquivo de idioma não encontrado.",
            "NOT_FOUND_SYSTEM_MODULE": "Módulo do sistema \' %s \' não encontrado.",
            "MODE_ERROR": "Erro do modo.",
            "NOT_ALLOWED": "Você não está autorizado a processar essa ação",
            "NOT_ALLOWED_SYSTEM": "Como permissões do sistema não permitem que você realize essa ação",
            "STORAGE_SIZE_EXCEED": "O tamanho máximo de armazenamento %s foi excedido.",
            "UNABLE_TO_CREATE_DIRECTORY": "Não foi concebido para criar uma pasta %s.",
            "UNABLE_TO_OPEN_DIRECTORY": "Não foi possível abrir pasta %s.",
            "UPLOAD_FILES_SMALLER_THAN": "Favor enviar apenas arquivos menores que %s.",
            "UPLOAD_IMAGES_ONLY": "Favor enviar apenas imagens. Outros arquivos não são aceitos.",
            "UPLOAD_IMAGES_TYPE_JPEG_GIF_PNG": "Favor enviar apenas imagens no formato JPG, GIF ou PNG.",
            "action_copy": "Copiar",
            "action_delete": "Excluir",
            "action_download": "Baixar",
            "action_extract": "Extrair",
            "action_move": "Mover",
            "action_rename": "Renomear",
            "action_replace": "Substituir",
            "action_select": "Selecionar",
            "action_upload": "Enviar",
            "browse": "Navegar...",
            "cancel": "Cancelar",
            "close": "Fechar",
            "clipboard_clear": "Limpar",
            "clipboard_clear_full": "Limpar área de transferência?",
            "clipboard_cleared": "Área de transferência limpa.",
            "clipboard_copy": "Copiar",
            "clipboard_copy_full": "Copiar selecionado",
            "clipboard_cut": "Cortar",
            "clipboard_cut_full": "Cortar selecionado",
            "clipboard_empty": "Área de transferência vazia.",
            "clipboard_paste": "Colar",
            "clipboard_paste_full": "Colar aqui",
            "confirm_delete": "Tem certeza em excluir esse item?",
            "confirm_delete_multiple": "Tem certeza em excluir %s itens?",
            "copied": "URL copiada!",
            "copy_to_clipboard": "Copiar para área de transferência",
            "could_not_retrieve_folder": "Não foi possível ler o conteúdo da pasta.",
            "create_folder": "Criar pasta",
            "created": "Criado",
            "current_folder": "Pasta atual",
            "default_foldername": "Nova pasta",
            "dimensions": "Dimensões",
            "editor_edit": "Editar arquivo",
            "editor_save": "Salvar",
            "editor_quit": "Sair",
            "filter_audio": "Arquivos de áudio",
            "filter_archive": "Arquivos",
            "filter_image": "Arquivos de imagens",
            "filter_media": "Arquivos de mídia",
            "filter_office": "Documentos",
            "filter_reset": "Todos os arquivos",
            "filter_video": "Arquivos de vídeo",
            "grid_view": "Alternar para visualizar em ícone.",
            "help_move": "O uso de \'../\' é proibido. Você pode acessar a pasta raiz usando \'/\'.",
            "items": "Arquivos/Pastas",
            "list_view": "Alternar para visualizar na lista.",
            "loading_data": "Transferindo dados ...",
            "modified": "Modificado",
            "name": "Nome",
            "nav_home": "Vá para a pasta raiz",
            "nav_level_up": "Ir para a pasta acima",
            "nav_refresh": "Atualizar arquivo/pasta",
            "new_filename": "Entre com o nome para o novo arquivo",
            "new_folder": "Nova pasta",
            "no": "Não",
            "no_foldername": "Nenhum nome foi fornecido.",
            "of": "de",
            "parentfolder": "Pasta anterior",
            "prompt_extract": "Extrair para ...",
            "prompt_foldername": "Digite nome da nova pasta",
            "prompt_move": "Mover para ...",
            "prompt_move_multiple": "Mover itens %s para ...",
            "search": "Buscar",
            "search_reset": "Restaurar",
            "search_results": "Search results",
            "search_string_empty": "Search string is empty",
            "size": "Tamanho",
            "successful_added_file": "Novo arquivo adicionado com sucesso.",
            "successful_added_folder": "Novo pasta adicionada com sucesso.",
            "successful_copied": "Copiado com sucesso.",
            "successful_delete": "Excluiu com sucesso.",
            "successful_edit": "Conteúdo atualizado com sucesso.",
            "successful_extracted": "Extraído com sucesso.",
            "successful_moved": "Movido com sucesso.",
            "successful_processed": "%s de %s solicitações realizadas ​​com sucesso.",
            "successful_rename": "Renomeou com sucesso.",
            "successful_replace": "Arquivo sobreposto com sucesso.",
            "summary_files": "Arquivos",
            "summary_folders": "Pastas",
            "summary_size": "Tamanho",
            "summary_title": "Resumo de armazenamento",
            "support_fm": "Filemanager é open source, por favor, nos ajudem!",
            "type": "Tipo",
            "unit_bytes": "Bytes",
            "unit_gb": "Gb",
            "unit_kb": "Kb",
            "unit_mb": "Mb",
            "upload_action_abort": "Abortar",
            "upload_action_delete": "Excluir",
            "upload_action_info": "Informação",
            "upload_action_resume": "Retornar",
            "upload_action_start": "Começar",
            "upload_aborted": "O carregamento foi abortado. O arquivo não foi descarregado ou parcialmente descarregado.",
            "upload_choose_file": "Selecione um arquivo para enviar.",
            "upload_dropzone_message": "Soltar arquivos aqui para enviar",
            "upload_file_size_limit": "O limite de tamanho do arquivo (por arquivo) é: %s",
            "upload_file_too_big": "O arquivo é muito grande.",
            "upload_file_type_invalid": "Você não pode enviar arquivos desse tipo.",
            "upload_files_number_limit": "Só são permitidos %s envios simultâneos.",
            "upload_failed": "Nenhum arquivo foi carregado.",
            "upload_failed_details": "Passe o mouse sobre um arquivo na fila para ver informações detalhadas.",
            "upload_successful_file": "Arquivo carregado com sucesso.",
            "upload_successful_files": "Todos os arquivos carregados com sucesso.",
            "upload_partially": "Alguns arquivos falharam durante o carregamento.",
            "version": "Versão",
            "yes": "Sim"
        }';
    }
}