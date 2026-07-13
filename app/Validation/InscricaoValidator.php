<?php

declare(strict_types=1);

namespace App\Validation;

use App\Core\Provas;
use App\Core\Str;
use App\Models\Escola;
use App\Models\Serie;

/**
 * Validação servidor da inscrição pública.
 * Todos os campos são obrigatórios (definição da campanha).
 * Compatível com PHP 7.1+
 */
final class InscricaoValidator
{
    /** @var array */
    private $errors = [];

    /** @var array */
    private $data = [];

    /** @var bool */
    private $checkDb;

    public function __construct($checkDb = true) {
        $this->checkDb = $checkDb;
        $this->errors = [];
        $this->data = [];
    }

    /**
     * @param array $input Dados brutos ($_POST).
     * @return bool true quando válido; erros em errors(), dados em data().
     */
    public function validate($input) {
        $this->errors = [];
        $this->data = [];

        $this->validarNome('aluno_nome', $input, 'Informe o nome completo do estudante.');
        $this->validarNascimento($input);
        $this->validarSerie($input);
        $this->validarEscola($input);
        $this->validarDataProva($input);
        $this->validarTexto('escola_atual', $input, 'Informe a escola atual do estudante.', 2, 150);
        $this->validarNome('responsavel_nome', $input, 'Informe o nome completo do responsável.');
        $this->validarTexto('parentesco', $input, 'Informe o grau de parentesco.', 2, 60);
        $this->validarWhatsapp($input);
        $this->validarEmail($input);
        $this->validarTexto('cidade', $input, 'Informe a cidade onde vocês moram.', 2, 120);
        $this->validarConsentimento('consent_privacidade', $input, 'É necessário autorizar o uso dos dados para concluir a inscrição.');
        $this->validarConsentimento('consent_contato', $input, 'É necessário autorizar o contato da escola sobre o concurso.');

        return $this->errors === [];
    }

    // Polyfills para string functions (PHP 8.0+)
    private function str_contains($haystack, $needle) {
        return strpos($haystack, $needle) !== false;
    }
    private function str_starts_with($haystack, $needle) {
        return strpos($haystack, $needle) === 0;
    }

    private function validarNome($campo, $input, $msgVazio) {
        $valor = Str::clean((string) ($input[$campo] ?? ''));
        if ($valor === '') {
            $this->errors[$campo] = $msgVazio;
            return;
        }
        if (mb_strlen($valor) < 5 || !$this->str_contains($valor, ' ')) {
            $this->errors[$campo] = 'Informe nome e sobrenome.';
            return;
        }
        if (mb_strlen($valor) > 150) {
            $this->errors[$campo] = 'O nome informado é longo demais.';
            return;
        }
        if (!preg_match("/^[\p{L}\p{M}' .-]+$/u", $valor)) {
            $this->errors[$campo] = 'Use apenas letras no nome.';
            return;
        }
        $this->data[$campo] = $valor;
    }

    private function validarNascimento($input) {
        $valor = Str::clean((string) ($input['aluno_nascimento'] ?? ''));
        if ($valor === '') {
            $this->errors['aluno_nascimento'] = 'Informe a data de nascimento do estudante.';
            return;
        }
        // Aceita dd/mm/aaaa (máscara do formulário) e aaaa-mm-dd (input date)
        if (preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $valor, $m)) {
            $dia = (int) $m[1];
            $mes = (int) $m[2];
            $ano = (int) $m[3];
        } elseif (preg_match('#^(\d{4})-(\d{2})-(\d{2})$#', $valor, $m)) {
            $ano = (int) $m[1];
            $mes = (int) $m[2];
            $dia = (int) $m[3];
        } else {
            $this->errors['aluno_nascimento'] = 'Data inválida. Use o formato dd/mm/aaaa.';
            return;
        }
        if (!checkdate($mes, $dia, $ano)) {
            $this->errors['aluno_nascimento'] = 'Esta data não existe no calendário.';
            return;
        }
        $data = sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
        $anoAtual = (int) date('Y');
        if ($ano < $anoAtual - 25 || $data >= date('Y-m-d')) {
            $this->errors['aluno_nascimento'] = 'Confira a data de nascimento informada.';
            return;
        }
        $this->data['aluno_nascimento'] = $data;
    }

    private function validarSerie($input) {
        $id = (int) ($input['serie_id'] ?? 0);
        if ($id <= 0) {
            $this->errors['serie_id'] = 'Escolha a série pretendida.';
            return;
        }
        if ($this->checkDb && !Serie::existsAtiva($id)) {
            $this->errors['serie_id'] = 'Série inválida. Escolha uma das opções disponíveis.';
            return;
        }
        $this->data['serie_id'] = $id;
    }

    private function validarEscola($input) {
        $id = (int) ($input['escola_id'] ?? 0);
        if ($id <= 0) {
            $this->errors['escola_id'] = 'Escolha a escola em que deseja estudar.';
            return;
        }
        if ($this->checkDb && !Escola::existsAtiva($id)) {
            $this->errors['escola_id'] = 'Escola inválida. Escolha uma das opções disponíveis.';
            return;
        }
        $this->data['escola_id'] = $id;
    }

    private function validarDataProva($input) {
        $valor = Str::clean((string) ($input['data_prova'] ?? ''));
        if ($valor === '') {
            $this->errors['data_prova'] = 'Escolha a data da prova.';
            return;
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor) || strtotime($valor) === false) {
            $this->errors['data_prova'] = 'Data de prova inválida.';
            return;
        }
        if (isset($this->errors['escola_id'])) {
            return;
        }
        if ($this->checkDb) {
            $escola = Escola::find((int) ($this->data['escola_id'] ?? 0));
            if ($escola === null || !Provas::dataPermitida($valor, $escola)) {
                $this->errors['data_prova'] = 'Escolha uma data disponível para a unidade.';
                return;
            }
        }
        $this->data['data_prova'] = $valor;
    }

    private function validarTexto($campo, $input, $msgVazio, $min, $max) {
        $valor = Str::clean((string) ($input[$campo] ?? ''));
        if ($valor === '') {
            $this->errors[$campo] = $msgVazio;
            return;
        }
        if (mb_strlen($valor) < $min) {
            $this->errors[$campo] = 'Valor curto demais.';
            return;
        }
        if (mb_strlen($valor) > $max) {
            $this->errors[$campo] = 'Valor longo demais.';
            return;
        }
        $this->data[$campo] = $valor;
    }

    private function validarWhatsapp($input) {
        $bruto = (string) ($input['whatsapp'] ?? '');
        $digitos = Str::digits($bruto);
        if ($digitos === '') {
            $this->errors['whatsapp'] = 'Informe o WhatsApp do responsável.';
            return;
        }
        // Aceita DDD + número (10 ou 11 dígitos, celular começa com 9)
        if (strlen($digitos) === 13 && $this->str_starts_with($digitos, '55')) {
            $digitos = substr($digitos, 2);
        }
        if (strlen($digitos) < 10 || strlen($digitos) > 11) {
            $this->errors['whatsapp'] = 'Número inválido. Informe DDD + número.';
            return;
        }
        $ddd = (int) substr($digitos, 0, 2);
        if ($ddd < 11 || $ddd > 99) {
            $this->errors['whatsapp'] = 'DDD inválido.';
            return;
        }
        if (strlen($digitos) === 11 && $digitos[2] !== '9') {
            $this->errors['whatsapp'] = 'Número de celular inválido.';
            return;
        }
        if (preg_match('/^(\d)\1+$/', substr($digitos, 2))) {
            $this->errors['whatsapp'] = 'Número inválido.';
            return;
        }
        $this->data['whatsapp'] = $digitos;
    }

    private function validarEmail($input) {
        $valor = mb_strtolower(Str::clean((string) ($input['email'] ?? '')), 'UTF-8');
        if ($valor === '') {
            $this->errors['email'] = 'Informe o e-mail do responsável.';
            return;
        }
        if (mb_strlen($valor) > 190 || filter_var($valor, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors['email'] = 'E-mail inválido. Confira o endereço digitado.';
            return;
        }
        $this->data['email'] = $valor;
    }

    private function validarConsentimento($campo, $input, $msg) {
        $valor = $input[$campo] ?? '';
        if ($valor !== '1' && $valor !== 1 && $valor !== 'on' && $valor !== true) {
            $this->errors[$campo] = $msg;
            return;
        }
        $this->data[$campo] = 1;
    }

    /** @return array */
    public function errors() {
        return $this->errors;
    }

    /** @return array */
    public function data() {
        return $this->data;
    }
}
