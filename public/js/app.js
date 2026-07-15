async function api(action,data={}){const res=await fetch(`/api/${action}`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':window.csrf},body:JSON.stringify(data)});return res.json();}
function loadClients(){api('clients').then(d=>{const box=document.getElementById('clients-list');if(!d.clients?.length)return box.innerHTML='<p>Нет подключений</p>';box.innerHTML=d.clients.map(c=>`<div class="client-card"><div><span class="status-dot"></span>${c.name}<br><small>${c.virt_ip}|${c.since}|↓${c.in}↑${c.out}</small></div><button class="btn-del" onclick="revoke('${c.name}')">🗑 Удалить</button></div>`).join('');});}
function revoke(name){if(!confirm(`Отозвать ${name}?`))return;api('revoke',{client:name}).then(r=>{alert(r.output);if(r.output?.startsWith('SUCCESS'))loadClients();});}
function initAddForm(){document.getElementById('add-form').onsubmit=async e=>{e.preventDefault();const name=document.getElementById('client-name').value.trim();const res=await api('add',{client:name});document.getElementById('add-result').textContent=res.output||res.error;if(res.output?.startsWith('SUCCESS'))document.getElementById('client-name').value='';};}
function initConsole(){window.runCmd=async()=>{const cmd=document.getElementById('cmd-input').value.trim();const res=await api('console',{cmd});document.getElementById('cmd-output').textContent=res.output||res.error;};}
function loadStatus(){api('status').then(r=>{document.getElementById('status-output').textContent=r.output;});}

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('clients-list')) {
        loadClients();
    }

    if (document.getElementById('add-form')) {
        initAddForm();
    }

    if (document.getElementById('cmd-input')) {
        initConsole();
    }

    if (document.getElementById('status-output')) {
        loadStatus();
    }
});
