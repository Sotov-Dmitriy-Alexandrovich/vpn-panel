@extends('layouts.app')

@section('content')

    <style>
        .client-box{
            margin-top: 20px;
        }
        .client-card{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:30px;
            background:#fff;
            border:1px solid #e5e7eb;
            border-radius:22px;
            padding:24px;

            transition:.25s;
        }

        .client-card:hover{
            transform:translateY(-2px);
            box-shadow:0 15px 35px rgba(0,0,0,.08);
            cursor:pointer;
        }

        .client-left{
            display:flex;
            align-items:center;
            gap:18px;
        }

        .status{
            width:18px;
            height:18px;
            border-radius:50%;
            flex-shrink:0;
            position:relative;
        }

        .status.online{
            background:#22c55e;
            box-shadow:0 0 0 8px rgba(34,197,94,.15);
        }

        .status.offline{
            background:#ef4444;
            box-shadow:0 0 0 8px rgba(239,68,68,.15);
        }

        .client-name{
            font-size:22px;
            font-weight:700;
            color:#111827;
        }

        .client-ip{
            color:#6b7280;
            margin-top:4px;
            font-size:15px;
        }

        .client-right{
            display:flex;
            gap:45px;
            text-align:right;
            flex-wrap:wrap;
        }

        .info{
            min-width:120px;
        }

        .label{
            color:#9ca3af;
            font-size:13px;
        }

        .value{
            margin-top:4px;
            font-weight:700;
            color:#111827;
            font-size:17px;
        }

        .time{
            min-width:220px;
        }

        #clients-container{
            display:flex;
            flex-direction:column;
            gap:18px;
        }

        @media(max-width:900px){

            .client-card{
                flex-direction:column;
                align-items:flex-start;
            }

            .client-right{
                width:100%;
                justify-content:space-between;
                text-align:left;
                gap:20px;
            }

            .client-name{
                font-size:19px;
            }

        }
    </style>

    <div class="max-w-7xl mx-auto px-6 py-8">

        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold">
                Подключённые клиенты
            </h1>

            <div id="last-update" class="text-gray-500"></div>
        </div>

        <div id="clients-container" class="client-box"></div>

        <div id="no-clients" class="hidden text-center py-24 text-gray-400 text-xl">

        </div>

    </div>

    <script>

        function loadClients(){

            fetch('/api/clients')
                .then(r=>r.json())
                .then(data=>{

                    const container=document.getElementById('clients-container');
                    const empty=document.getElementById('no-clients');

                    container.innerHTML='';

                    if(!data.clients || data.clients.length===0){

                        container.style.display='none';
                        empty.classList.remove('hidden');

                    }else{

                        container.style.display='flex';
                        empty.classList.add('hidden');

                        data.clients.forEach(client=>{

                            const card=document.createElement('div');

                            card.className='client-card';

                            card.innerHTML=`

<div class="client-left">

<div class="status ${client.status==='connected'?'online':'offline'}"></div>

<div>

<div class="client-name">
${client.name}
</div>

<div class="client-ip">
${client.real_ip}
</div>

</div>

</div>

<div class="client-right">

<div class="info">
<div class="label">Получено</div>
<div class="value">${client.in}</div>
</div>

<div class="info">
<div class="label">Отправлено</div>
<div class="value">${client.out}</div>
</div>

<div class="info time">
<div class="label">Подключён</div>
<div class="value">${client.since}</div>
</div>

</div>

`;

                            container.appendChild(card);

                        });

                    }

                    document.getElementById('last-update').innerHTML=
                        'Обновлено: '+new Date().toLocaleTimeString('ru-RU');

                });

        }

        loadClients();

        setInterval(loadClients,1000);

    </script>

@endsection
