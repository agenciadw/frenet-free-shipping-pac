=== Frenet Free Shipping PAC ===
Contributors: David William da Costa  
Tags: shipping, delivery, frete, frenet  
Requires at least: 5.0  
Tested up to: 7.6  
Requires PHP: 7.6  
Stable tag: 1.0  
License: GPLv2 or later  
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Este plugin integra o serviço PAC da Frenet ao método de Frete Grátis do WooCommerce, exibindo o prazo de entrega em dias úteis diretamente no label do Frete Grátis, sem ocultar outros métodos de entrega.

== Features ==

- Captura o prazo de entrega do PAC Frenet e adiciona ao Free Shipping.
- Não remove nem oculta outros métodos de frete.
- Validação de CEP no formato 99999-999 ou 99999999.
- Proteção CSRF via nonce na página de configurações.
- Logs de erro em caso de falha na comunicação com a API Frenet.
- Exibição de contador de dias restantes no carrinho (opcional).

== Installation ==

1. Faça upload da pasta **frenet-free-shipping-pac** para `/wp-content/plugins/`.
2. Ative o plugin em **Plugins > Plugins instalados**.
3. Acesse **WooCommerce > Ajustes > Entrega** e localize a seção **Frenet Free Shipping PAC**.
4. Preencha os campos:
   - **Chave**: Chave de acesso da API Frenet.
   - **Senha**: Senha da API Frenet.
   - **Token**: Token de autenticação.
   - **CEP Origem**: CEP de origem para cálculo (99999-999 ou 99999999).
5. Salve alterações.

== Usage ==

1. Defina a política de Frete Grátis no WooCommerce (valor mínimo, cupom, etc.).
2. No checkout, quando a condição de frete grátis for atendida, o método aparecerá como:  
   **Frete grátis (até X dias úteis)** — onde “X” vem da API do PAC Frenet.
3. Todos os outros métodos de envio continuam visíveis normalmente.

== Changelog ==
= 1.0 =

- Versão inicial completa integrando o PAC da Frenet com o Frete Grátis.
- Adiciona configuração de Chave, Senha, Token e CEP de Origem no admin.
- Validação de CEP e sanitização de inputs.
- Proteção CSRF via nonce.
- Lógica para capturar e exibir o prazo do PAC Frenet no label do Free Shipping.
- Prioridade ajustada para garantir que o prazo seja copiado após o cálculo da Frenet.
- Não oculta outros métodos de envio; apenas substitui o label do Free Shipping.
- Exibição opcional de contador de dias restantes no carrinho.
- Logging de erros em caso de falha na comunicação com a API Frenet.

== Frequently Asked Questions ==
= Quais versões de PHP são suportadas? =
Requer PHP 7.6 ou superior.

= O plugin oculta outros métodos de entrega? =
Não. Ele apenas substitui o label do método PAC pelo Free Shipping com prazo.

= Como validar o CEP corretamente? =
Aceita formatos com ou sem hífen: `99999-999` ou `99999999`.

== Screenshots ==

1. **Configurações**  
   ![Configurações](/assets/screenshot-1.jpg)
2. **Checkout com prazo**  
   ![Checkout](/assets/screenshot-2.jpg)

== Support ==
Abra uma issue em https://github.com/agenciadw/frenet-free-shipping-pac ou contate o autor para dúvidas e sugestões.
