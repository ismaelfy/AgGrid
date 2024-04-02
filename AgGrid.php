class AgGrid {
    private $filterModel;
    private $sortModel;
    private $paginationParams;

    public function __construct() {
        // Obtener datos del filtro, modelo de clasificación y parámetros de paginación del cuerpo de la solicitud POST
        $postData = json_decode(file_get_contents('php://input'), true);

        $this->filterModel = isset($postData['filterModel']) ? $postData['filterModel'] : [];
        $this->sortModel = isset($postData['sortModel']) ? $postData['sortModel'] : [];
        $this->paginationParams = isset($postData['paginationParams']) ? $postData['paginationParams'] : ['startRow' => 0, 'endRow' => 10];
    }

    public function buildQuery() {
        $whereSql = $this->whereSql();
        $orderBySql = $this->orderBySql();
        $limitSql = $this->limitSql();

        return 'SELECT * FROM your_table' . $whereSql . $orderBySql . $limitSql;
    }

    private function whereSql() {
        $whereParts = [];
        foreach ($this->filterModel as $columnKey => $filter) {
            if (!empty($filter['filter'])) {
                switch ($filter['filterType']) {
                    case 'text':
                        $whereParts[] = $this->processTextFilter($columnKey, $filter);
                        break;
                    case 'number':
                        $whereParts[] = $this->processNumberFilter($columnKey, $filter);
                        break;
                    // Agregar casos para otros tipos de filtro si es necesario
                }
            }
        }

        if (!empty($whereParts)) {
            return ' WHERE ' . implode(' AND ', $whereParts);
        }

        return '';
    }

    private function processTextFilter($columnKey, $filter) {
        switch ($filter['type']) {
            case 'equals':
                return "$columnKey = '{$filter['filter']}'";
            case 'notEqual':
                return "$columnKey != '{$filter['filter']}'";
            case 'contains':
                return "$columnKey LIKE '%{$filter['filter']}%'";
            case 'notContains':
                return "$columnKey NOT LIKE '%{$filter['filter']}%'";
            case 'startsWith':
                return "$columnKey LIKE '{$filter['filter']}%'";
            case 'endsWith':
                return "$columnKey LIKE '%{$filter['filter']}'";
            // Agregar más casos según sea necesario
            default:
                return ''; // Manejar otros casos si es necesario
        }
    }

    private function processNumberFilter($columnKey, $filter) {
        switch ($filter['type']) {
            case 'equals':
                return "$columnKey = {$filter['filter']}";
            case 'notEqual':
                return "$columnKey != {$filter['filter']}";
            case 'greaterThan':
                return "$columnKey > {$filter['filter']}";
            case 'greaterThanOrEqual':
                return "$columnKey >= {$filter['filter']}";
            case 'lessThan':
                return "$columnKey < {$filter['filter']}";
            case 'lessThanOrEqual':
                return "$columnKey <= {$filter['filter']}";
            case 'inRange':
                return "($columnKey BETWEEN {$filter['filter']} AND {$filter['filterTo']})";
            // Agregar más casos según sea necesario
            default:
                return ''; // Manejar otros casos si es necesario
        }
    }

    private function orderBySql() {
        $orderByParts = [];
        foreach ($this->sortModel as $sort) {
            $orderByParts[] = "{$sort['colId']} {$sort['sort']}";
        }

        if (!empty($orderByParts)) {
            return ' ORDER BY ' . implode(', ', $orderByParts);
        }

        return '';
    }

    private function limitSql() {
        return ' OFFSET ' . $this->paginationParams['startRow'] . ' ROWS FETCH NEXT ' . ($this->paginationParams['endRow'] - $this->paginationParams['startRow']) . ' ROWS ONLY';
    }
}

// Ejemplo de uso:

// Crear una instancia de AgGrid
$agGrid = new AgGrid();

// Obtener la consulta SQL generada
$query = $agGrid->buildQuery();
echo "Generated SQL Query: $query\n";
