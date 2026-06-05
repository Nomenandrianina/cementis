{{-- <div class="col-sm-12 col-lg-8">
    <div class="row">
        <!-- Name Field -->
        <div class="form-group col-sm-6">
            {!! Form::label('name', 'Name:',['class' => 'required']) !!}
            {!! Form::text('name', null, ['class' => 'form-control']) !!}
        </div>

        <!-- Title Field -->
        <div class="form-group col-sm-6">
            {!! Form::label('title', 'Title:') !!}
            {!! Form::text('title', null, ['class' => 'form-control']) !!}
        </div>

        <!-- Guard Name Field -->
        <div class="form-group col-sm-6">
            {!! Form::label('guard_name', 'Guard Name:') !!}
            {!! Form::select('guard_name', ['web' => 'web', 'api' => 'api'], null, ['class' => 'form-control custom-select']) !!}
        </div>


        <!-- Description Field -->
        <div class="form-group col-sm-12 col-lg-12">
            {!! Form::label('description', 'Description:') !!}
            {!! Form::textarea('description', null, ['class' => 'form-control','rows'=>5]) !!}
        </div>
    </div>


</div>

<!-- Permission Field -->
<div class="form-group col-sm-12 col-lg-4 ">
    @php
    $groupPermission = $allPermission->groupBy('module');
    @endphp
    <div class="permission">
        {!! Form::label('permission', 'Permission:') !!}
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            @foreach ( $groupPermission as $key=>$permissions)

            <li class="nav-item">
                <div class="d-flex">
                    {!! Form::checkbox('checkAll', null,false, ['class' => 'check-all']) !!}
                    <a href="#" class="nav-link flex-fill">
                        <i class="nav-icon fas fa-shield-virus"></i>
                        <p>
                            {{fast_trans('common.module.'.$key,[],$key)}}
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>

                </div>

                <ul class="nav nav-treeview">
                    @foreach ($permissions as $permission)
                    <li class="nav-item">
                        <a class="nav-link">
                            {!! Form::checkbox('permission_data[]', $permission->id,(isset($role)&&count($role->permission_data)>0&&isset($role->permission_data[$permission->id])?true:false), ['class' => 'check-one']) !!}
                            <i class="nav-icon fas fa-circle"></i>
                            <p>
                                {{fast_trans('common.permission.'.$permission->name,[],$permission->title)}}
                            </p>
                        </a>
                    </li>
                    @endforeach

                </ul>
            </li>
            @endforeach

        </ul>
    </div>
</div>
@push('page_css')
<style>
    .permission {
        max-height: 400px;
        height: 100%;
        overflow: auto;
    }

    .permission .nav .nav-treeview {
        margin-left: 20px;
    }

    .permission .nav-sidebar .nav-item {
        position: relative;
    }

    .permission .nav-sidebar .nav-item .check-all {
        margin: .7rem 0rem;
    }

    .permission .nav-sidebar .nav-item .nav-link {
        padding: 0.5rem 0.5rem;
    }

    .permission .nav-sidebar .nav-item .nav-link p {
        width: unset;
        visibility: unset;
        margin-left: 0;
        -webkit-animation-name: unset;
        animation-name: unset;
        -webkit-animation-duration: unset;
        animation-duration: unset;
        -webkit-animation-fill-mode: unset;
        animation-fill-mode: unset;
    }

    .permission .nav-sidebar .menu-open .nav-link i.right {
        -webkit-transform: rotate(-90deg);
        transform: rotate(-90deg);
    }
</style>
@endpush

@push('page_scripts')
<script>
    $(function() {
        $('.permission .check-all').click(function() {
            var check = this.checked;
            $(this).parents('.nav-item').find('.check-one').prop("checked", check);
        })
        $('.permission .check-one').click(function() {
            var parentItem = $(this).parents('.nav-treeview').parents('.nav-item');
            var check = $(parentItem).find('.check-one:checked').length == $(parentItem).find('.check-one').length;
            $(parentItem).find('.check-all').prop("checked", check)
        });
        $('.permission .check-all').each(function() {
            var parentItem = $(this).parents('.nav-item');
            var check = $(parentItem).find('.check-one:checked').length == $(parentItem).find('.check-one').length;
            $(parentItem).find('.check-all').prop("checked", check)
        })
    });
</script>
@endpush --}}


<div class="col-sm-12 col-lg-7">
    <div class="card border-0 shadow-sm h-100" style="border-radius: 8px; background: #ffffff; border: 1px solid #e4e4e7 !important;">
        <div class="card-body p-4 p-md-5">
            <h3 class="h6 font-weight-bold text-uppercase mb-4" style="color: #09090b; letter-spacing: 0.05em; font-size: 0.8rem;">
                Propriétés du Rôle
            </h3>
            
            <div class="row">
                <div class="form-group col-sm-6 mb-4">
                    <div class="d-flex justify-content-between">
                        {!! Form::label('name', 'Code système (Name)', ['class' => 'text-zinc-500 font-weight-medium small mb-2 d-block']) !!}
                        <span class="text-danger small">*</span>
                    </div>
                    {!! Form::text('name', null, [
                        'class' => 'form-control form-modern-input text-monospace', 
                        'placeholder' => 'ex: admin',
                        'style' => 'border-radius: 6px; padding: 10px 14px; font-size: 0.9rem; border: 1px solid #e4e4e7;'
                    ]) !!}
                </div>

                <div class="form-group col-sm-6 mb-4">
                    {!! Form::label('title', 'Nom affiché (Title)', ['class' => 'text-zinc-500 font-weight-medium small mb-2 d-block']) !!}
                    {!! Form::text('title', null, [
                        'class' => 'form-control form-modern-input', 
                        'placeholder' => 'ex: Administrateur',
                        'style' => 'border-radius: 6px; padding: 10px 14px; font-size: 0.9rem; border: 1px solid #e4e4e7;'
                    ]) !!}
                </div>

                <div class="form-group col-12 mb-4">
                    {!! Form::label('guard_name', 'Guard d\'authentification', ['class' => 'text-zinc-500 font-weight-medium small mb-2 d-block']) !!}
                    {!! Form::select('guard_name', ['web' => 'web', 'api' => 'api'], null, [
                        'class' => 'form-control custom-select form-modern-input', 
                        'style' => 'border-radius: 6px; padding: 10px 14px; font-size: 0.9rem; border: 1px solid #e4e4e7; height: auto; appearance: none;'
                    ]) !!}
                </div>

                <div class="form-group col-12 mb-0">
                    {!! Form::label('description', 'Description des accès', ['class' => 'text-zinc-500 font-weight-medium small mb-2 d-block']) !!}
                    {!! Form::textarea('description', null, [
                        'class' => 'form-control form-modern-input', 
                        'placeholder' => 'Décrivez les responsabilités de ce groupe...', 
                        'rows' => 4,
                        'style' => 'border-radius: 6px; padding: 10px 14px; font-size: 0.9rem; border: 1px solid #e4e4e7; resize: none;'
                    ]) !!}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-sm-12 col-lg-5">
    @php
        $groupPermission = $allPermission->groupBy('module');
    @endphp
    
    <div class="card border-0 shadow-sm h-100" style="border-radius: 8px; background: #ffffff; border: 1px solid #e4e4e7 !important;">
        <div class="card-body p-4 p-md-5 d-flex flex-column h-100">
            <div class="mb-3">
                <h3 class="h6 font-weight-bold text-uppercase mb-1" style="color: #09090b; letter-spacing: 0.05em; font-size: 0.8rem;">
                    Habilitations & Privilèges
                </h3>
                <p class="text-muted small mb-0">Cochez les modules pour ouvrir les sous-droits.</p>
            </div>
            
            <div class="modern-permission-scroll flex-fill pr-1">
                <ul class="permission-tree-root list-unstyled m-0">
                    @foreach ($groupPermission as $key => $permissions)
                    <li class="module-card-item mb-2 rounded border" style="border-color: #e4e4e7 !important; transition: all 0.2s;">
                        
                        <div class="d-flex align-items-center justify-content-between p-3 module-header-trigger" style="background: #fafafa; cursor: pointer;">
                            <div class="d-flex align-items-center gap-3" style="gap: 12px;">
                                <div class="modern-checkbox-wrapper">
                                    {!! Form::checkbox('checkAll', null, false, ['class' => 'check-all modern-cb']) !!}
                                </div>
                                <span class="font-weight-semibold text-dark text-sm ml-2">
                                    <i class="fas fa-shield-alt mr-2 text-muted" style="font-size: 0.85rem;"></i>
                                    {{ fast_trans('common.module.'.$key, [], $key) }}
                                </span>
                            </div>
                            <span class="text-muted toggle-icon-indicator transition-all" style="font-size: 0.8rem;">
                                <i class="fas fa-chevron-down"></i>
                            </span>
                        </div>

                        <div class="module-permissions-child border-top p-3" style="display: none; border-color: #f4f4f5 !important; background: #ffffff;">
                            <div class="d-flex flex-column gap-2.5" style="gap: 10px;">
                                @foreach ($permissions as $permission)
                                <label class="d-flex align-items-center m-0 py-1 px-2 rounded perm-item-row transition-all" style="cursor: pointer;">
                                    {!! Form::checkbox('permission_data[]', $permission->id, (isset($role) && count($role->permission_data) > 0 && isset($role->permission_data[$permission->id]) ? true : false), ['class' => 'check-one modern-cb']) !!}
                                    <span class="text-secondary text-sm ml-3 font-weight-medium" style="font-size: 0.875rem;">
                                        {{ fast_trans('common.permission.'.$permission->name, [], $permission->title) }}
                                    </span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

@push('page_css')
<style>
    .text-zinc-500 { color: #71717a; }
    .text-sm { font-size: 0.875rem !important; }
    .font-weight-semibold { font-weight: 600 !important; }
    
    /* Inputs uniformes */
    .form-modern-input:focus {
        border-color: #18181b !important;
        box-shadow: 0 0 0 2px rgba(24, 24, 27, 0.05) !important;
        outline: none;
    }
    select.form-modern-input {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 12px 12px;
    }

    /* Scrollbar discrète style Mac/SaaS */
    .modern-permission-scroll {
        max-height: 415px;
        overflow-y: auto;
    }
    .modern-permission-scroll::-webkit-scrollbar { width: 5px; }
    .modern-permission-scroll::-webkit-scrollbar-track { background: transparent; }
    .modern-permission-scroll::-webkit-scrollbar-thumb { background: #e4e4e7; border-radius: 10px; }

    /* Custom Checkbox minimaliste plat (style Tailwind) */
    input[type="checkbox"].modern-cb {
        accent-color: #18181b; /* Case noire pure au check */
        width: 16px;
        height: 16px;
        cursor: pointer;
    }

    /* Effets de lignes au survol */
    .perm-item-row:hover {
        background-color: #f4f4f5;
    }
    .module-card-item:hover {
        border-color: #cbd5e1 !important;
    }
    
    /* Rotation icône accordéon */
    .module-card-item.is-open .toggle-icon-indicator {
        transform: rotate(-180deg);
    }
</style>
@endpush

@push('page_scripts')
<script>
    $(function() {
        // Logique de déploiement type Accordion React épuré (remplace le Treeview d'AdminLTE)
        $('.module-header-trigger').click(function(e) {
            if ($(e.target).hasClass('check-all') || $(e.target).hasClass('modern-checkbox-wrapper')) {
                return; // Ne pas fermer/ouvrir si on clique sur la checkbox parente
            }
            var item = $(this).closest('.module-card-item');
            item.toggleClass('is-open');
            item.find('.module-permissions-child').slideToggle(200);
        });

        // Gestionnaire d'état des cases à cocher (votre logique optimisée)
        $('.permission-tree-root .check-all').click(function() {
            var check = this.checked;
            var childContainer = $(this).closest('.module-card-item').find('.module-permissions-child');
            childContainer.find('.check-one').prop("checked", check);
            
            // Auto-déploie le conteneur si on coche le parent pour donner du feedback visuel
            if(check && !$(this).closest('.module-card-item').hasClass('is-open')) {
                $(this).closest('.module-card-item').addClass('is-open').find('.module-permissions-child').slideDown(200);
            }
        });

        $('.permission-tree-root .check-one').click(function() {
            var parentItem = $(this).closest('.module-card-item');
            var check = parentItem.find('.check-one:checked').length == parentItem.find('.check-one').length;
            parentItem.find('.check-all').prop("checked", check);
        });

        // Initialisation de l'état des parents au chargement
        $('.permission-tree-root .check-all').each(function() {
            var parentItem = $(this).closest('.module-card-item');
            var totalChilds = parentItem.find('.check-one').length;
            var checkedChilds = parentItem.find('.check-one:checked').length;
            
            var check = totalChilds === checkedChilds && totalChilds > 0;
            $(this).prop("checked", check);
            
            // Si des enfants sont cochés mais pas tous, on peut aussi l'ouvrir par défaut
            if(checkedChilds > 0) {
                parentItem.addClass('is-open').find('.module-permissions-child').show();
            }
        });
    });
</script>
@endpush