variable "app_name" {
    type = string
    default = "AllCoin"
}

variable "app_timezone" {
    type = string
    default = "Europe/Paris"
}

variable "log_channel" {
    type = string
    default = "stderr"
}

variable "dynamodb_table_name" {
    type = string
}

variable "app_version" {
    type = string
}
