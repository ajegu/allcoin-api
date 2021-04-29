variable "app_name" {
    type = string
}

variable "app_timezone" {
    type = string
}

variable "log_channel" {
    type = string
}

variable "dynamodb_table_name" {
    type = string
}

variable "lambda_name" {
    type = string
    default = "BinanceOrderAnalyzer"
}
