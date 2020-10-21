{* HEADER *}

{foreach from=$elementNames item=elementName}
  <div class="crm-section">
    <div class="label">{$form.$elementName.label}</div>
    <div class="content">{$form.$elementName.html}</div>
    <div class="clear"></div>
  </div>
{/foreach}

<script>
  var sqlexport_queries = {$sqlexport_queries};

  {literal}
  function update_sql() {
    console.log(sqlexport_queries);
    for (const name in sqlexport_queries) {
      if (sqlexport_queries.hasOwnProperty(name)) { 
        if (name == CRM.$('#saved_query').val()) {
          console.log("Yip", name, sqlexport_queries[name]);
          CRM.$('#name').val(name);
          CRM.$('#sql').val(sqlexport_queries[name]);
        }
      }
    };
  }

  CRM.$(document).ready(function() {
    update_sql();
    CRM.$('#saved_query').change(function() {
      update_sql();    
    });
  });

  {/literal}
</script>
{* FOOTER *}
<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
