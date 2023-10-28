interface CalculatedTax {
    price: number,
    tax: number,
    taxRate: number
}

interface GridColumn {
    property: string,
    dataIndex?: string,
    label: string,
    allowResize?: boolean,
    width?: string,
    align?: string,
    multiLine?: boolean,
    inlineEdit?: boolean
    visible?: boolean,
}

enum LineItemStatus {
    OPEN = 'open',
    SHIPPED = 'shipped',
    SHIPPED_PARTIALLY = 'shipped_partially',
    RETURN_REQUESTED = 'return_requested',
    RETURNED = 'returned',
    RETURNED_PARTIALLY = 'returned_partially',
    CANCELLED = 'cancelled',
}

enum DocumentTypes {
    STORNO = 'storno',
    CREDIT_NOTE = 'credit_note',
    PARTIAL_CANCELLATION = 'partial_cancellation',
    INVOICE = 'invoice',
    DELIVERY_NOTE = 'delivery_note',
}

export type {
    CalculatedTax,
    GridColumn,
}

export {
    LineItemStatus,
    DocumentTypes
}
