import type {
  LoginRequest,
  LoginResponse,
  RegisterRequest,
  RegisterResponse,
  RefreshResponse,
  VerifyResponse,
  MeResponse,
  UsersResponse,
  UserDetailResponse,
  ApiError,
} from "@/types/auth"

const API_BASE_URL =
  process.env.NEXT_PUBLIC_API_URL || "http://localhost:8080"

function buildUrl(path: string, params?: Record<string, string>): string {
  const url = new URL(`${API_BASE_URL}/api.php`)
  url.searchParams.set("path", path)
  if (params) {
    Object.entries(params).forEach(([key, value]) => {
      url.searchParams.set(key, value)
    })
  }
  return url.toString()
}

let accessToken: string | null = null
let refreshToken: string | null = null
let refreshPromise: Promise<string | null> | null = null

export function setTokens(access: string | null, refresh: string | null) {
  accessToken = access
  refreshToken = refresh
}

export function getAccessToken() {
  return accessToken
}

export function clearTokens() {
  accessToken = null
  refreshToken = null
  refreshPromise = null
}

async function refreshAccessToken(): Promise<string | null> {
  if (!refreshToken) return null

  if (refreshPromise) return refreshPromise

  refreshPromise = (async () => {
    try {
      const res = await fetch(buildUrl("auth/refresh"), {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ refresh_token: refreshToken }),
      })

      if (!res.ok) {
        clearTokens()
        return null
      }

      const data: RefreshResponse = await res.json()
      if (data.success && data.data.access_token) {
        accessToken = data.data.access_token
        return accessToken
      }

      clearTokens()
      return null
    } catch {
      clearTokens()
      return null
    } finally {
      refreshPromise = null
    }
  })()

  return refreshPromise
}

async function apiFetch<T>(
  path: string,
  options: RequestInit = {},
  params?: Record<string, string>,
  retry = true
): Promise<T> {
  const url = buildUrl(path, params)
  const headers: Record<string, string> = {
    "Content-Type": "application/json",
    ...(options.headers as Record<string, string>),
  }

  if (accessToken) {
    headers["Authorization"] = `Bearer ${accessToken}`
  }

  const res = await fetch(url, { ...options, headers })

  if (res.status === 401 && retry && refreshToken) {
    const newToken = await refreshAccessToken()
    if (newToken) {
      return apiFetch<T>(path, options, params, false)
    }
    throw new ApiRequestError("Session expired. Please login again.", 401)
  }

  const data = await res.json()

  if (!res.ok) {
    const error = data as ApiError
    throw new ApiRequestError(
      error.message || error.error || "An error occurred",
      res.status,
      error.errors
    )
  }

  return data as T
}

export class ApiRequestError extends Error {
  status: number
  fieldErrors?: Record<string, string>

  constructor(
    message: string,
    status: number,
    fieldErrors?: Record<string, string>
  ) {
    super(message)
    this.name = "ApiRequestError"
    this.status = status
    this.fieldErrors = fieldErrors
  }
}

// --- Auth endpoints ---

export async function login(data: LoginRequest): Promise<LoginResponse> {
  return apiFetch<LoginResponse>("auth/login", {
    method: "POST",
    body: JSON.stringify(data),
  })
}

export async function register(
  data: RegisterRequest
): Promise<RegisterResponse> {
  return apiFetch<RegisterResponse>("auth/register", {
    method: "POST",
    body: JSON.stringify(data),
  })
}

export async function verifyToken(): Promise<VerifyResponse> {
  return apiFetch<VerifyResponse>("auth/verify")
}

// --- User endpoints ---

export async function getMe(): Promise<MeResponse> {
  return apiFetch<MeResponse>("me")
}

export async function getUsers(
  limit = 50,
  offset = 0
): Promise<UsersResponse> {
  return apiFetch<UsersResponse>("users", {}, {
    limit: String(limit),
    offset: String(offset),
  })
}

export async function getUserById(id: number): Promise<UserDetailResponse> {
  return apiFetch<UserDetailResponse>("user", {}, { id: String(id) })
}

export async function updateProfile(
  name: string,
  csrfToken = ""
): Promise<{ success: boolean; message: string }> {
  return apiFetch("profile", {
    method: "POST",
    body: JSON.stringify({ name, csrf_token: csrfToken }),
  })
}

export async function changePassword(data: {
  current_password: string
  new_password: string
  confirm_password: string
  csrf_token?: string
}): Promise<{ success: boolean; message: string }> {
  return apiFetch("change-password", {
    method: "POST",
    body: JSON.stringify(data),
  })
}
