export interface User {
  id: number
  email: string
  name: string
  is_active: number | boolean
  last_login: string | null
  created_at: string
  updated_at: string
}

export interface Role {
  id: number
  name: string
  description: string
}

export interface Permission {
  id: number
  name: string
  description: string
}

export interface LoginRequest {
  email: string
  password: string
}

export interface RegisterRequest {
  name: string
  email: string
  password: string
}

export interface LoginResponse {
  success: boolean
  message: string
  data: {
    user: User
    access_token: string
    refresh_token: string
    token_type: string
    expires_in: number
  }
}

export interface RegisterResponse {
  success: boolean
  message: string
  data: {
    user_id: number
  }
}

export interface RefreshResponse {
  success: boolean
  data: {
    access_token: string
    token_type: string
    expires_in: number
  }
}

export interface VerifyResponse {
  valid: boolean
  data: {
    user_id: number
    email: string
    expires_at: number
    time_remaining: number
  }
}

export interface MeResponse {
  success: boolean
  data: {
    user: User
    roles: Role[] | string[]
    permissions: Permission[] | string[]
  }
}

export interface UsersResponse {
  success: boolean
  data: {
    users: User[]
    pagination: {
      total: number
      limit: number
      offset: number
    }
  }
}

export interface UserDetailResponse {
  success: boolean
  data: {
    user: User & { roles?: string[] }
    permissions: Permission[] | string[]
  }
}

export interface ApiError {
  error?: string
  message?: string
  success?: false
  errors?: Record<string, string>
}

export interface AuthState {
  user: User | null
  roles: string[]
  permissions: string[]
  accessToken: string | null
  refreshToken: string | null
  isAuthenticated: boolean
  isLoading: boolean
}
