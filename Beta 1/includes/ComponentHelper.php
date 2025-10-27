<?php
/**
 * Componentes Reutilizables del Sistema
 * Genera elementos comunes de la interfaz
 */

class ComponentHelper {
    
    /**
     * Genera una tabla estándar con filtros y paginación
     */
    public static function renderDataTable($config) {
        $html = '';
        
        // Filtros
        if (isset($config['filters'])) {
            $html .= '<div class="card mb-4"><div class="card-body">';
            $html .= '<form method="GET" class="row g-3">';
            
            foreach ($config['filters'] as $filter) {
                $html .= '<div class="col-md-' . ($filter['size'] ?? 4) . '">';
                
                switch ($filter['type']) {
                    case 'text':
                        $html .= "<input type='text' class='form-control' name='{$filter['name']}' placeholder='{$filter['placeholder']}' value='" . htmlspecialchars($filter['value'] ?? '') . "'>";
                        break;
                    case 'select':
                        $html .= "<select name='{$filter['name']}' class='form-select'>";
                        $html .= "<option value=''>{$filter['placeholder']}</option>";
                        foreach ($filter['options'] as $value => $text) {
                            $selected = ($filter['value'] ?? '') == $value ? 'selected' : '';
                            $html .= "<option value='{$value}' {$selected}>{$text}</option>";
                        }
                        $html .= "</select>";
                        break;
                    case 'date':
                        $html .= "<input type='date' class='form-control' name='{$filter['name']}' value='" . htmlspecialchars($filter['value'] ?? '') . "'>";
                        break;
                }
                
                $html .= '</div>';
            }
            
            $html .= '<div class="col-md-4">';
            $html .= '<button type="submit" class="btn btn-outline-primary"><i class="fas fa-search"></i> Buscar</button>';
            $html .= '<a href="' . $config['clear_url'] . '" class="btn btn-outline-secondary ms-2"><i class="fas fa-times"></i> Limpiar</a>';
            $html .= '</div>';
            
            $html .= '</form></div></div>';
        }
        
        // Tabla
        $html .= '<div class="card"><div class="card-body">';
        $html .= '<div class="table-responsive">';
        $html .= '<table class="table table-striped table-hover">';
        
        // Headers
        $html .= '<thead class="table-dark"><tr>';
        foreach ($config['columns'] as $column) {
            $html .= '<th>' . $column['title'] . '</th>';
        }
        if (isset($config['actions'])) {
            $html .= '<th>Acciones</th>';
        }
        $html .= '</tr></thead>';
        
        // Body
        $html .= '<tbody>';
        if (empty($config['data'])) {
            $colspan = count($config['columns']) + (isset($config['actions']) ? 1 : 0);
            $html .= '<tr><td colspan="' . $colspan . '" class="text-center py-4">';
            $html .= '<i class="' . ($config['empty_icon'] ?? 'fas fa-inbox') . ' fa-2x text-muted mb-2"></i>';
            $html .= '<p class="text-muted">' . ($config['empty_message'] ?? 'No se encontraron datos') . '</p>';
            $html .= '</td></tr>';
        } else {
            foreach ($config['data'] as $row) {
                $html .= '<tr>';
                foreach ($config['columns'] as $column) {
                    $value = $row[$column['field']] ?? '';
                    
                    if (isset($column['format'])) {
                        switch ($column['format']) {
                            case 'currency':
                                $value = '$' . number_format($value, 0, ',', '.');
                                break;
                            case 'date':
                                $value = date('d/m/Y', strtotime($value));
                                break;
                            case 'datetime':
                                $value = date('d/m/Y H:i', strtotime($value));
                                break;
                            case 'badge':
                                $badgeClass = $column['badge_map'][$value] ?? 'secondary';
                                $value = "<span class='badge bg-{$badgeClass}'>" . ucfirst(str_replace('_', ' ', $value)) . "</span>";
                                break;
                        }
                    }
                    
                    $html .= '<td>' . $value . '</td>';
                }
                
                // Acciones
                if (isset($config['actions'])) {
                    $html .= '<td><div class="btn-group" role="group">';
                    foreach ($config['actions'] as $action) {
                        $html .= "<button class='btn btn-sm btn-outline-{$action['color']} btn-action' onclick=\"{$action['onclick']}\">";
                        $html .= "<i class='{$action['icon']}'></i>";
                        $html .= '</button>';
                    }
                    $html .= '</div></td>';
                }
                
                $html .= '</tr>';
            }
        }
        $html .= '</tbody></table></div>';
        
        // Paginación
        if (isset($config['pagination'])) {
            $html .= LayoutManager::renderPagination(
                $config['pagination']['current'],
                $config['pagination']['total'],
                $config['pagination']['url'],
                $config['pagination']['params'] ?? []
            );
        }
        
        $html .= '</div></div>';
        
        return $html;
    }
    
    /**
     * Genera un modal estándar
     */
    public static function renderModal($config) {
        $html = "<div class='modal fade' id='{$config['id']}' tabindex='-1'>";
        $html .= "<div class='modal-dialog" . ($config['size'] ?? '') . "'>";
        $html .= "<div class='modal-content'>";
        
        // Header
        $html .= "<div class='modal-header'>";
        $html .= "<h5 class='modal-title' id='{$config['id']}Title'>{$config['title']}</h5>";
        $html .= "<button type='button' class='btn-close' data-bs-dismiss='modal'></button>";
        $html .= "</div>";
        
        // Body
        if (isset($config['form'])) {
            $html .= "<form method='POST' id='{$config['id']}Form'>";
            foreach ($config['form']['hidden'] ?? [] as $name => $value) {
                $html .= "<input type='hidden' name='{$name}' id='{$name}' value='{$value}'>";
            }
        }
        
        $html .= "<div class='modal-body'>";
        
        if (isset($config['form']['fields'])) {
            foreach ($config['form']['fields'] as $field) {
                $html .= self::renderFormField($field);
            }
        } else {
            $html .= $config['content'] ?? '';
        }
        
        $html .= "</div>";
        
        // Footer
        $html .= "<div class='modal-footer'>";
        foreach ($config['buttons'] ?? [] as $button) {
            $html .= "<button type='{$button['type']}' class='btn btn-{$button['color']}'";
            if (isset($button['dismiss'])) {
                $html .= " data-bs-dismiss='modal'";
            }
            if (isset($button['onclick'])) {
                $html .= " onclick=\"{$button['onclick']}\"";
            }
            $html .= ">{$button['text']}</button>";
        }
        $html .= "</div>";
        
        if (isset($config['form'])) {
            $html .= "</form>";
        }
        
        $html .= "</div></div></div>";
        
        return $html;
    }
    
    /**
     * Genera un campo de formulario
     */
    private static function renderFormField($field) {
        $html = '<div class="' . ($field['container_class'] ?? 'mb-3') . '">';
        
        if (isset($field['label'])) {
            $html .= "<label class='form-label'>{$field['label']}</label>";
        }
        
        switch ($field['type']) {
            case 'text':
            case 'email':
            case 'tel':
            case 'number':
            case 'date':
            case 'password':
                $html .= "<input type='{$field['type']}' class='form-control' name='{$field['name']}' id='{$field['name']}'";
                if (isset($field['placeholder'])) $html .= " placeholder='{$field['placeholder']}'";
                if (isset($field['required']) && $field['required']) $html .= " required";
                if (isset($field['readonly']) && $field['readonly']) $html .= " readonly";
                $html .= ">";
                break;
                
            case 'select':
                $html .= "<select name='{$field['name']}' id='{$field['name']}' class='form-select'";
                if (isset($field['required']) && $field['required']) $html .= " required";
                $html .= ">";
                if (isset($field['placeholder'])) {
                    $html .= "<option value=''>{$field['placeholder']}</option>";
                }
                foreach ($field['options'] ?? [] as $value => $text) {
                    $html .= "<option value='{$value}'>{$text}</option>";
                }
                $html .= "</select>";
                break;
                
            case 'textarea':
                $html .= "<textarea name='{$field['name']}' id='{$field['name']}' class='form-control'";
                if (isset($field['rows'])) $html .= " rows='{$field['rows']}'";
                if (isset($field['placeholder'])) $html .= " placeholder='{$field['placeholder']}'";
                if (isset($field['required']) && $field['required']) $html .= " required";
                $html .= "></textarea>";
                break;
                
            case 'checkbox':
                $html .= "<div class='form-check'>";
                $html .= "<input type='checkbox' name='{$field['name']}' id='{$field['name']}' class='form-check-input'>";
                $html .= "<label class='form-check-label' for='{$field['name']}'>{$field['label']}</label>";
                $html .= "</div>";
                break;
        }
        
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Genera cards de estadísticas
     */
    public static function renderStatsCards($stats) {
        $html = '<div class="row mb-4">';
        
        foreach ($stats as $stat) {
            $html .= '<div class="col-md-3 mb-3">';
            $html .= '<div class="card bg-white shadow-sm">';
            $html .= '<div class="card-body text-center">';
            $html .= '<h3 class="mb-2">' . $stat['value'] . '</h3>';
            $html .= '<p class="mb-0 text-muted">' . $stat['label'] . '</p>';
            if (isset($stat['icon'])) {
                $html .= '<i class="' . $stat['icon'] . ' text-' . ($stat['color'] ?? 'primary') . ' fa-2x mt-2"></i>';
            }
            $html .= '</div></div></div>';
        }
        
        $html .= '</div>';
        return $html;
    }
}
?>
